<?php
require path('/Services/MenuService');
$menu = (new MenuService())->get_menu_oktmo();
// dd($menu);
?>
<!-- 
<script>console.log('menu.view.php', local);</script> -->
<?php
function oktmo_styles($outer_key)
{
    if (!isset($_GET['oktmo']) || empty($_GET['oktmo'])) {
        if ($outer_key == '65608000') {
            return '';
        } else {
            return 'hidden';
        }
    } else {
        if ($outer_key == $_GET['oktmo']) {
            return '';
        } else {
            return 'hidden';
        }
    }

}
?>
<ul id="menu" class="menu-main">
    <?php foreach ($menu as $outer_key => $outer_menu): ?>
        <div>
            <li id="trigger_<?= $outer_key ?>" class="outer menu_item"><?= $outer_menu['item']['name'] ?></li>
            <ul id="menu_<?= $outer_key ?>" class="inner_menu <?= oktmo_styles($outer_key) ?>">
                <?php foreach ($outer_menu['children'] as $inner_menu): ?>
                    <li class="inner menu_item">
                        <a href="/?oktmo=<?= $outer_key ?>&oktmo_d=<?= $inner_menu['kodzprn'] ?>">
                            <?= $inner_menu['name'] ?>
                        </a>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endforeach ?>
</ul>