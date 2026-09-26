<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2025 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die('Restricted access');

extract($displayData);

$field->layout = 'joomla.form.field.password';
?>
<style>
    .cf-verify-wrap { /* already position-relative from markup */
    }

    .cf-float-alert {
        position: absolute;
        left: 0;
        right: 0;
        top: -0.5rem; /* sits just above the input */
        transform: translateY(-100%);
        z-index: 1060; /* above form controls; < modal (1050–1060) */
        pointer-events: none; /* don’t steal focus/mouse */
    }

    .cf-float-alert .alert {
        pointer-events: auto; /* allow dismiss button if you add one later */
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15);
    }

    .cf-verify-wrap .password-group {
        width: 75%;
    }
</style>
<div class="cf-verify-wrap d-flex align-items-start gap-2 position-relative">
    <?= $field->input; ?>
    <!-- New verify button beside the field -->
    <button type="button" class="btn btn-outline-primary" id="cfVerifyBtn" aria-label="Verify Cloudflare API token">
        Verify Token
    </button>

    <!-- Floating alert container (JS will fill it) -->
    <div class="cf-float-alert" aria-live="polite" aria-atomic="true"></div>
</div>
<script>
    (() => {
        const JchVerifyCfApi = () => {
            window.JCH_CF_VERIFY_ENDPOINT = window.JCH_CF_VERIFY_ENDPOINT ||
                'index.php?option=com_jchoptimize&task=CfVerify.verify&format=json';

            const btn = document.getElementById('cfVerifyBtn');            // existing button
            const tokenEl = document.getElementById('jform_cf_api_token');     // token input
            const zoneEl = document.getElementById('jform_cf_zone_id');       // zone input
            const wrap = btn?.closest('.cf-verify-wrap');                   // from earlier markup
            const alertHost = wrap?.querySelector('.cf-float-alert');
            const badge = document.getElementById('cfZoneVerifiedBadge');    // optional badge near zone field

            function showAlert(html, type = 'success', ttl = 4000) {
                if (!alertHost) return;
                alertHost.innerHTML = '';
                const el = document.createElement('div');
                el.className = `alert alert-${type} fade show`;
                el.role = 'alert';
                el.innerHTML = html;
                alertHost.appendChild(el);
                setTimeout(() => {
                    el.classList.remove('show');
                    setTimeout(() => alertHost.innerHTML = '', 200);
                }, ttl);
            }

            function setBusy(b, busy) {
                if (!b) return;
                if (busy) {
                    b.disabled = true;
                    b.dataset._orig = b.innerHTML;
                    b.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                     <span class="ms-2">Verifying…</span>`;
                } else {
                    b.disabled = false;
                    if (b.dataset._orig) b.innerHTML = b.dataset._orig;
                }
            }

            btn?.addEventListener('click', async () => {
                const token = tokenEl?.value.trim() || '';
                const zone = zoneEl?.value.trim() || '';

                if (!token) {
                    showAlert('Please enter your Cloudflare API token.', 'warning');
                    return;
                }

                // Joomla CSRF token name (J4/J5)
                const csrfName = (window.Joomla && Joomla.getOptions) ? (Joomla.getOptions('csrf.token') || '') : '';

                setBusy(btn, true);
                badge?.classList.add('d-none');

                try {
                    const fd = new FormData();
                    fd.append('token', token);
                    if (zone) fd.append('zone_id', zone);
                    if (csrfName) fd.append(csrfName, '1');

                    const res = await fetch(window.JCH_CF_VERIFY_ENDPOINT, {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin'
                    });
                    const json = await res.json().catch(() => ({}));

                    // Expect shape from PHP: { success, token_status, zone_read?, purge_ok?, verified_at? }
                    const ok = res.ok && (json.success === true || (json.data && json.data.success === true));
                    const data = (json.data && json.data.success !== undefined) ? json.data : json;

                    if (ok) {
                        // Build a nice message
                        let msg = 'Token verified';
                        if (data.token_status) msg += ` (<strong>${data.token_status}</strong>)`;
                        if (zone) {
                            if (data.zone_read) msg += ', zone readable';
                            if (data.purge_ok) msg += ', purge permission confirmed';
                        }
                        msg += '.';

                        showAlert(msg, 'success');

                        // Show "Verified" badge when zone was part of the check and both read+purge passed
                        if (zone && data.zone_read && data.purge_ok && badge) {
                            badge.classList.remove('d-none');
                            // Optional: show time since verification
                            if (data.verified_at) {
                                const dt = new Date(data.verified_at);
                                if (!isNaN(+dt)) {
                                    const mins = Math.max(0, Math.round((Date.now() - dt.getTime()) / 60000));
                                    badge.textContent = mins < 1 ? 'Verified just now'
                                        : mins === 1 ? 'Verified 1 min ago'
                                            : `Verified ${mins} mins ago`;
                                } else {
                                    badge.textContent = 'Verified';
                                }
                            } else {
                                badge.textContent = 'Verified';
                            }
                        }
                    } else {
                        const msg = json.message || (json.data && json.data.message) || 'Verification failed.';
                        showAlert(msg, 'danger');
                    }
                } catch (err) {
                    showAlert('Network error. Please try again.', 'danger');
                } finally {
                    setBusy(btn, false);
                }
            });
        }

        (document.readyState === 'loading')
            ? document.addEventListener('DOMContentLoaded', JchVerifyCfApi, {once: true})
            : JchVerifyCfApi();
    })();
</script>

