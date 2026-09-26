/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
const jchPlatform = (
    function () {

        let jch_ajax_url_optimizeimages = 'index.php?option=com_jchoptimize&view=OptimizeImage&task=optimizeimage&format=json';
        let jch_ajax_url_multiselect = 'index.php?option=com_jchoptimize&controller=Ajax&task=multiselect&format=json';
        let jch_ajax_url_smartcombine = 'index.php?option=com_jchoptimize&controller=Ajax&task=smartcombine&format=json';
        let jch_loader_image_url = '../media/com_jchoptimize/core/images/loader.gif';

        const setting_prefix = 'jform';

        /**
         *
         * @param int
         * @param id
         */
        const applyAutoSettings = function (int, id) {
            const auto_settings = document.querySelectorAll("li.quickicon a.auto-setting");
            const toggles = document.querySelectorAll("li.quickicon a.auto-setting .dashicon-toggle div.fa");
            let image = document.createElement("img");
            image.src = jch_loader_image_url;
            image.className = 'jch-pending-image';

            for (const toggle of toggles) {
                const parent = toggle.parentNode;
                parent.replaceChild(image.cloneNode(true), toggle);
            }

            let url = "index.php?option=com_jchoptimize&view=ApplyAutoSetting&format=json&autosetting=s" + int;

            postData(url)
                .then(data => {
                    for (const auto_setting of auto_settings) {
                        auto_setting.classList.remove("enabled");
                        auto_setting.classList.add("disabled");
                    }

                    //Turn off all toggles
                    const pendingImages = document.querySelectorAll("li.quickicon a.auto-setting img.jch-pending-image");
                    let toggleDiv = document.createElement('div');
                    toggleDiv.className = "fs-6 fa fa-toggle-off";

                    for (const image of pendingImages) {
                        image.parentNode.replaceChild(toggleDiv.cloneNode(true), image);
                    }

                    //if the response returned without error then the setting is applied
                    if (data.success) {
                        const current_setting = document.querySelector('#' + id + ' a');
                        current_setting.className = "auto-setting enabled w-100 position-relative";
                        const activeToggle = current_setting.querySelector(".dashicon-toggle div.fa");
                        activeToggle.className = "fs-6 fa fa-toggle-on"

                        const combineFilesEnableInput = document.querySelector('.jch-dash-icons-switcher input');
                        combineFilesEnableInput.checked = true;
                        const combineFilesEnableLabel = document.querySelector('.jch-dash-icons-switcher label');
                        combineFilesEnableLabel.textContent = 'Enabled';
                    }

                })
                .catch(err => console.log(err));
        };

        const toggleCombineFilesEnable = function () {
            const checkboxParentDiv = document.getElementById('combine-files-enable');
            const autoSettings = document.querySelectorAll("li.quickicon a.auto-setting");

            for (const autoSetting of autoSettings) {
                const autoSettingToggle = autoSetting.querySelector('.dashicon-toggle div.fa');

                autoSetting.classList.remove('enabled');
                autoSetting.classList.add('disabled');

                autoSettingToggle.classList.remove('fa-toggle-on');
                autoSettingToggle.classList.add('fa-toggle-off');
            }

            postData(
                "index.php?option=com_jchoptimize&view=ToggleSetting&format=json&setting=combine_files_enable"
            )
                .then(data => {
                    if (data.auto !== false) {
                        const enabled_auto_setting = document.querySelector('#' + data.auto + ' a');
                        enabled_auto_setting.classList.remove("disabled");
                        enabled_auto_setting.classList.add("enabled");

                        const toggle = enabled_auto_setting.querySelector('.dashicon-toggle div.fa');
                        toggle.classList.remove('fa-toggle-off');
                        toggle.classList.add('fa-toggle-on');
                    }

                    const input = checkboxParentDiv.querySelector('input.form-check-input');
                    input.checked = data.enabled;
                    const label = checkboxParentDiv.querySelector('label.form-check-label');
                    label.textContent = data.enabled ? 'Enabled' : 'Disabled';
                })
        }
        /**
         *
         * @param setting
         * @param id
         */
        const toggleSetting = function (setting, id) {
            let a = document.querySelector("#" + id + " a");
            let toggle = document.querySelector("#" + id + " div.dashicon-toggle div.fa");
            const image = document.createElement("img");
            image.src = jch_loader_image_url;
            image.className = 'jch-pending-image';

            const oldToggle = toggle.parentNode.replaceChild(image, toggle);

            let url = "index.php?option=com_jchoptimize&view=ToggleSetting&format=json&setting=" + setting;

            postData(url)
                .then(data => {
                    a.classList.remove("enabled", "disabled");
                    a.classList.add(data.class);

                    let toggleClass = data.class2 === 'enabled' ? 'fa-toggle-on' : 'fa-toggle-off';

                    if (id === 'optimize-css-delivery') {
                        let unused_css = document.querySelector("#reduce-unused-css a");
                        unused_css.classList.remove("enabled", "disabled");
                        unused_css.classList.add(data.class2);

                        let unusedCssToggle = unused_css.querySelector('.dashicon-toggle .fa');
                        unusedCssToggle.classList.remove('fa-toggle-on', 'fa-toggle-off');
                        unusedCssToggle.classList.add(toggleClass);
                    }

                    if (id === 'reduce-unused-css') {
                        let optimize_css = document.querySelector("#optimize-css-delivery a");
                        optimize_css.classList.remove("enabled", 'disabled');
                        optimize_css.classList.add(data.class2);

                        let optimizeCssToggle = optimize_css.querySelector('.dashicon-toggle .fa');
                        optimizeCssToggle.classList.remove('fa-toggle-on', 'fa-toggle-off');
                        optimizeCssToggle.classList.add(toggleClass);
                    }


                    if (setting === 'integrated_page_cache_enable') {
                        let mode_switcher_indicator = document.getElementById("mode-switcher-indicator");
                        if (mode_switcher_indicator !== null) {
                            mode_switcher_indicator.classList.remove(
                                "production",
                                "development",
                                "page-cache-only",
                                "page-cache-disabled"
                            );
                            mode_switcher_indicator.classList.add(data.status_class);
                        }

                        let page_cache_status = document.getElementById("page-cache-status");
                        if (page_cache_status !== null) {
                            page_cache_status.innerHTML = data.page_cache_status;
                        }
                    }

                    const pendingImage = a.querySelector('img.jch-pending-image');
                    oldToggle.className = 'fs-6 fa fa-toggle-' + (data.enabled ? 'on' : 'off');
                    pendingImage.parentNode.replaceChild(oldToggle, pendingImage);
                })
                .catch(err => console.log(err));
        };

        const getCacheInfo = function () {
            let url = 'index.php?option=com_jchoptimize&view=CacheInfo&format=json';

            postData(url)
                .then(data => {
                    let numFiles = document.querySelectorAll('.numFiles-container');
                    let fileSize = document.querySelectorAll('.fileSize-container');

                    numFiles.forEach((container) => {
                        container.innerHTML = data.numFiles + ' Files';
                    });

                    fileSize.forEach((container) => {
                        container.innerHTML = data.size;
                    });
                })
        };

        const loadBulkSettingsModal = function () {
            let modalLoaded = true;

            try {
                const modal = new bootstrap.Modal('#bulk-settings-modal-container', {
                    backdrop: 'static',
                    keyboard: false
                })
                modal.show();
            } catch (e) {
                modalLoaded = false;
            }

            if (!modalLoaded) {
                //Try with jQuery for joomla3
                jQuery('#bulk-settings-modal-container').modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: true
                });
            }
        };

        const submitForm = function () {
            Joomla.submitbutton('config.save.component.apply');
        };

        async function postData(url)
        {
            let ajaxUrl = new URL(url, window.location.toString());
            const response = await fetch(ajaxUrl, {
                method: 'GET',
                mode: 'cors',
                cache: 'no-cache',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                redirect: 'follow',
                referrerPolicy: 'no-referrer',
            });

            return response.json();
        }

        return {
            jch_ajax_url_multiselect: jch_ajax_url_multiselect,
            jch_ajax_url_optimizeimages: jch_ajax_url_optimizeimages,
            jch_ajax_url_smartcombine: jch_ajax_url_smartcombine,
            setting_prefix: setting_prefix,
            applyAutoSettings: applyAutoSettings,
            toggleSetting: toggleSetting,
            submitForm: submitForm,
            getCacheInfo: getCacheInfo,
            loadBulkSettingsModal: loadBulkSettingsModal,
            toggleCombineFilesEnable: toggleCombineFilesEnable
        }
    }
)();
