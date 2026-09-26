/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 *  @package   jchoptimize/core
 *  @author    Samuel Marshall <samuel@jch-optimize.net>
 *  @copyright Copyright (c) 2023 Samuel Marshall / JCH Optimize
 *  @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

import {v4 as uuidV4} from './uuid.v4.min.js';

const state = {
    totalFiles: 0,
    currentCnt: 0,
    numOptimized: 0,
    noWebpGenerated: 0,
    status: 'success',
    message: '',
    connectAttempts: 0,
    jobId: null,
    cursor: null,
};

const useWebSocket = (page, cookieObj) => {
    if (++state.connectAttempts > 10) {
        logMessage('Exceeded max connection attempts with WebSocket', 'error');
        reload();
        return;
    }

    const wssUrl = new URL('wss://websocket.jch-optimize.net:443/');
    const wsPageUrl = new URL(page);
    const evtSrcPageUrl = new URL(page);

    wsPageUrl.search += '&evtMsg=WebSocket';
    evtSrcPageUrl.search += '&evtMsg=EventSource';

    const webSocket = new WebSocket(wssUrl);
    const browserId = uuidV4();
    const websocketMsg = {id: browserId, role: 'browser', payload: {}};
    let connectionTimeoutId;

    webSocket.onerror = () => {
        console.log('Error connecting to WebSocket server. Switching to EventSource...');
        useEventSource(evtSrcPageUrl.toString(), cookieObj);
    };

    webSocket.onopen = () => {
        console.log('Connected to WebSocket server.');

        wsPageUrl.search += '&browserId=' + browserId;
        webSocket.send(JSON.stringify({...websocketMsg, type: 'identify'}));
        connectPHPWebSocketClient(wsPageUrl.toString());

        connectionTimeoutId = setTimeout(() => {
            console.log('PHP client taking too long to connect. Switching to EventSource...');
            webSocket.close();
            useEventSource(evtSrcPageUrl.toString(), cookieObj);
        }, 9000);
    };

    webSocket.onmessage = (event) => {
        let response;
        try {
            response = JSON.parse(event.data);
        } catch {
            logMessage('Invalid JSON received', 'danger');
            return;
        }

        const handlers = {
            clientsPaired: () => {
                console.log('PHP client connected.');
                clearTimeout(connectionTimeoutId);
                webSocket.send(JSON.stringify({
                    ...websocketMsg,
                    type: 'message',
                    payload: {data: cookieObj, type: 'optimize'}
                }));
            },
            addFileCount: data => addFileCount(data),
            fileOptimized: data => fileOptimized(data),
            alreadyOptimized: data => alreadyOptimized(data),
            optimizationFailed: data => optimizationFailed(data),
            webpGenerated: data => webpGenerated(data),
            requestRejected: data => requestRejected(data),
            apiError: data => {
                webSocket.close();
                apiError(data);
            },
            /*disconnected: data => {
                webSocket.close();
                apiError(data);
            },*/
            complete: data => {
                webSocket.close();
                complete(data);
            },
            batchComplete: (data) => {
                webSocket.close();
                onBatchComplete(data, page)
            },
            default: data => defaultMessage(data),
        };

        (handlers[response.type] || handlers.default)(response.data);
    };
};

const useEventSource = (page, cookieObj) => {
    document.cookie = 'jch_optimize_images_api=' + JSON.stringify({data: cookieObj, type: 'optimize'});

    const evtSource = new EventSource(page);
    evtSource.onopen = () => logMessage('Connection to EventSource server opened.', 'info');

    evtSource.addEventListener('error', () => {
        logMessage('EventSource failed', 'danger');
        reload();
    }, {once: true});

    const handlers = {
        message: data => defaultMessage(data),
        addFileCount: data => addFileCount(data),
        fileOptimized: data => fileOptimized(data),
        alreadyOptimized: data => alreadyOptimized(data),
        optimizationFailed: data => optimizationFailed(data),
        webpGenerated: data => webpGenerated(data),
        requestRejected: data => requestRejected(data),
        apiError: data => {
            evtSource.close();
            apiError(data);
        },
       /* disconnected: data => {
            evtSource.close();
            apiError(data);
        }, */
        complete: data => {
            evtSource.close();
            complete(data);
        },
        batchComplete: (data) => {
            evtSource.close();
            onBatchComplete(data, page)
        },
    };

    Object.keys(handlers).forEach(event => {
        evtSource.addEventListener(event, e => handlers[event](e.data));
    });
};

const connectPHPWebSocketClient = async (url) => {
    try {
        const response = await fetch(url, {method: 'GET', mode: 'cors', cache: 'no-cache', credentials: 'same-origin'});
        if (!response.ok) throw new Error(`Response status: ${response.status}`);
        console.log(await response.text());
    } catch (err) {
        console.error('Error starting server', err);
    }
};

const reload = () => {
    logMessage('Reloading in <span id="reload-timer">10</span> seconds...', 'info');
    let countdown = 10;

    const updateTimer = () => {
        document.querySelector('#reload-timer').textContent = (--countdown).toString();
        if (countdown === 0) clearInterval(intervalId);
    };

    const intervalId = setInterval(updateTimer, 1000);

    setTimeout(() => {
        const redirect = new URL(window.location.href);
        redirect.searchParams.append('status', state.status);
        redirect.searchParams.append('cnt', state.numOptimized);
        redirect.searchParams.append('webp', state.noWebpGenerated);
        redirect.searchParams.append('msg', encodeURIComponent(state.message));
        redirect.hash = '';
        window.location.href = redirect.toString();
    }, 10000);
};

const updateStatusBar = () => {
    const el = document.querySelector('#optimize-status');
    el.textContent = `Processed ${state.currentCnt} / ${state.totalFiles} files, ${state.numOptimized} optimized, ${state.noWebpGenerated} converted to WEBP format...`;
};

const updateProgressBar = () => {
    const progressBar = document.querySelector('#progressbar');
    progressBar.max = state.totalFiles;
    progressBar.value = state.currentCnt;
    const percent = state.totalFiles > 0 ? Math.floor(state.currentCnt / state.totalFiles * 100) : 0;
    progressBar.textContent = `${percent}%`;
};

const logMessage = (msg, cls) => {
    const el = document.querySelector('#optimize-log');
    const li = document.createElement('li');
    li.className = `alert p-1 my-1 alert-${cls}`;
    li.innerHTML = msg;
    el.appendChild(li);
    li.scrollIntoView({behavior: 'smooth', block: 'end'});
};

const addProgressBar = () => {
    //noinspection JSUnresolvedVariable
    new window.bootstrap.Modal('#optimize-images-modal-container', {backdrop: 'static', keyboard: false}).show();
    const modalBody = document.querySelector('#optimize-images-modal-container .modal-body');
    modalBody.innerHTML = `
        <progress id="progressbar">0%</progress>
        <div id="optimize-status">Gathering files to optimize. Please wait...</div>
        <div><ul id="optimize-log"></ul></div>
    `;
};

const addFileCount = data => {
    state.totalFiles += parseInt(data, 10);
    updateProgressBar();
    updateStatusBar();
};

const fileOptimized = data => {
    state.currentCnt++;
    state.numOptimized++;
    updateProgressBar();
    updateStatusBar();
    logMessage(data, 'success');
};

const alreadyOptimized = data => {
    state.currentCnt++;
    updateStatusBar();
    updateProgressBar();
    logMessage(data, 'secondary');
};

const optimizationFailed = data => {
    state.currentCnt++;
    updateStatusBar();
    updateProgressBar();
    logMessage(data, 'warning');
};

const webpGenerated = data => {
    state.noWebpGenerated++;
    updateStatusBar();
    logMessage(data, 'primary');
};

const requestRejected = data => {
    state.currentCnt++;
    updateStatusBar();
    updateProgressBar();
    logMessage(data, 'danger');
};

const apiError = data => {
    state.status = 'fail';
    state.message = data;
    logMessage(data, 'danger');
    reload();
};

const complete = data => {
    logMessage(`Done! Adding logs in folder ${data}`, 'info');
    reload();
};

const defaultMessage = data => logMessage(data, 'info');

const onBatchComplete = (raw, page) => {
    let data;
    try {
        data = typeof raw === 'string' ? JSON.parse(raw) : raw;
    } catch {
        data = {};
    }

    state.cursor = data?.cursor ?? null;

    // if not finished, immediately start next batch;
    // small delay gives PHP/infra a breather and reduces churn
    if (!data?.finished) {
        logMessage('Preparing next batch...');
        setTimeout(() => {
            // Always pass the latest cursor & same jobId
            const newCookieObj = {
                params: window.jchOptimizeImageData.params ?? {},
                jobId: state.jobId,
                cursor: state.cursor,
                firstRun: false
            };
            state.connectAttempts = 0;

            // Kick off the next batch – using the same page URL
            useWebSocket(page, newCookieObj);
        }, 250);
    } else {
        // all done
        complete(data?.finalLogDir ?? '');
    }
};

export const optimizeImages = (page, api_mode) => {
    if (!state.jobId) state.jobId = uuidV4();
    //noinspection JSUnresolvedVariable
    const cookieObj = {params: window.jchOptimizeImageData.params ?? {}};

    if (api_mode === 'manual') {
        const fileTree = document.querySelector('#file-tree-container');
        const root = fileTree?.querySelector('ul.jqueryFileTree li.root > a')?.dataset?.root ?? '';

        if (document.querySelectorAll('#files-container input[type=checkbox]:checked').length <= 0) {
            //noinspection JSUnresolvedVariable
            alert(window.jchOptimizeImageData.message ?? 'Please select files or subfolders to optimize');
            return false;
        }

        const subDirs = document.querySelectorAll('#files-container li.directory input[type=checkbox]:checked');
        cookieObj.subdirs = [...subDirs].map(item => item.value);
        cookieObj.filepack = [...document.querySelectorAll('#files-container li.file input[type=checkbox]:checked')]
            .map(item => {
                const file = {path: root + item.value};
                const parent = item.parentElement;

                const width = parent?.querySelector('input[name=width]')?.value;
                const height = parent?.querySelector('input[name=height]')?.value;

                if (width) file.width = width;
                if (height) file.height = height;

                return file;
            });
    }

    cookieObj.firstRun = true;
    cookieObj.jobId = state.jobId;
    if (state.cursor) cookieObj.cursor = state.cursor;

    addProgressBar();
    useWebSocket(page, cookieObj);
}

window.jchOptimizeImageApi = {optimizeImages};
