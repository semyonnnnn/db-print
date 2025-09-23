<table style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <?php foreach ($headers as $header): ?>
                <th style="border: 1px solid #ccc; padding: 5px; background-color: #eee;">
                    <?= htmlspecialchars($header) ?>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <?php foreach ($row as $cell): ?>
                    <td style="border: 1px solid #ccc; padding: 5px; background-color: #f9f9f9;">
                        <?= htmlspecialchars($cell) ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>