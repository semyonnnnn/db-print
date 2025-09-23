<?php
require path('Services/IndicatorsService');

$oktmo_d = $_GET['oktmo_d'] ?? 65608400;
$indicators = (new IndicatorsService($oktmo_d, 2019))->data['names'];
?>
<form class="indicators-main" method="POST" action="/generate_pdf.php" target="_blank">
    <?php foreach ($indicators as $outer_indicators): ?>
        <li class="ind_trigger" id="ind_trigger_<?= $outer_indicators['kodpok'] ?>"><?= $outer_indicators['namepok'] ?>
        </li>
        <ul class="ind_menu hidden" id="ind_menu_<?= $outer_indicators['kodpok'] ?>">
            <?php foreach ($outer_indicators['children'] as $inner): ?>
                <li>
                    <label class="checkbox-container" for="fd_<?= $inner['kodpokn'] ?>">

                        <input type="checkbox"
                            value="<?= $inner['namepok'] . "|" . $inner['nameei'] . "|" . $inner['numbers'][0] ?>"
                            name="<?= $inner['kodpokn'] . "|" . $outer_indicators['kodpok'] . "|{$outer_indicators['namepok']}" ?>"
                            id="fd_<?= $inner['kodpokn'] ?>">
                        <span class="fake-checkbox"></span>
                        <span class="indicators_data_span">
                            <span
                                id="fd_<?= $inner['kodpokn'] ?>"><?= $inner['namepok'] . ", " . $inner['nameei'] . ": " ?></span>
                            <?php foreach ($inner['numbers'] as $number): ?>
                                <span><?= $number ?></span>
                            <?php endforeach ?>
                        </span>
                    </label>
                </li>

            <?php endforeach ?>
        </ul>
    <?php endforeach ?>
    <button type="submit" class="submit_button">В документ</button>
</form>