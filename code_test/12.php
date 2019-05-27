<? function block_icons($list) { ?>
    <div class="swiper-slide">
        <ul class="mui-table-view mui-grid-view mui-grid-9">
            <? if (!empty($list)) {
                foreach ($list as &$cat) { ?>
                    <li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3">
                        <a href="<? if (false !== ($_val = cat_url($cat))) echo $_val; ?>">
                            <? if (!$cat['icon']) { ?>
                                <span class="mui-icon mui-icon-image"></span>
                            <? } else { ?>
                                <span class="mui-icon"
                                      style="background: url(<?= CDN ?><?= $cat['icon'] ?>);width:36px;height:36px;border-radius: 36px;background-size: cover;"></span>
                            <? } ?>
                            <div class="mui-media-body"><?= $cat['name'] ?></div>
                        </a></li>
                <? }
            } ?>
        </ul>
    </div>
<? } ?>
