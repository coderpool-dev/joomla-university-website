<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 *  @package   jchoptimize/core
 *  @author    Samuel Marshall <samuel@jch-optimize.net>
 *  @copyright Copyright (c) 2024 Samuel Marshall / JCH Optimize
 *  @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

?>

<ul id="jchsupportinfo" class="jchsupportinfo list-group list-group-flush">
    <li class="list-group-item">
        <span class="fa fa-code-fork me-2"></span> JCH Optimize Pro <?= JCH_VERSION; ?> Copyright 2025 &copy;
        <a href="https://www.jch-optimize.net/"> JCH Optimize</a>
    </li>
    <?php if (JCH_PRO) : ?>
    <li class="list-group-item">
        <div class="d-flex justify-content-between align-items-center">
           <div class="jchsupportinfo__optimize-desc">
               <span class="fa fa-cogs me-2"></span>
               Need help with configuring for best results? Check out our optimizing services.
           </div>
            <a href="https://www.jch-optimize.net/subscribes/subscribe-joomla/joomla-optimize/optimize-services-for-joomla-article.html" class="btn btn-primary btn-sm" target="_blank">
                Get Help!
            </a>
        </div>
    </li>
    <?php else : ?>
    <li class="list-group-item">
        <div class="d-flex justify-content-between align-items-center">
            <div class="jchsupportinfo__discount-desc">
                <span class="fa fa-unlock me-2"></span>
                Upgrade to the PRO version today with 20% off using <span>JCHGOPRO20</span>
            </div>
            <a href="https://www.jch-optimize.net/subscribes/subscribe-joomla/jmstarter/new/jmstarter.html?layout=default&coupon=JCHGOPRO20" class="btn btn-primary btn-sm" target="_blank">
                Go PRO!
            </a>
        </div>
    </li>
    <?php endif; ?>
</ul>