<?php

function getExpireDate($id)
{
    $dsn = 'mysql:host=localhost;dbname=test_db';
    $db = new PDO($dsn, 'root', 'iddqd');
    $activationDates = [];

    $stmt = $db->prepare('SELECT * from `license_activations` WHERE TO_OBJECT=:id');
    $stmt->execute(['id' => $id]);
    $dbRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($dbRows as $dbRow) {
        $currentDate = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', time()));
        $activationDate = DateTime::createFromFormat('Y-m-d H:i:s', $dbRow['ACTIVATION_DATE']);

        if ($currentDate->diff($activationDate)->days < 60) {
            $activationDates[] = $activationDate;
        }
    }

    if (empty($activationDates)) {
        return false;
    }

    $minDate = 0;

    if (count($activationDates) > 1) {
        for ($i = 0; $i < count($activationDates) - 1; $i++) {
            if ($activationDates[$i]->format('U') < $activationDates[$i + 1]->format('U')) {
                $minDate = $activationDates[$i];
            } elseif ($activationDates[$i]->format('U') > $activationDates[$i + 1]->format('U')) {
                $minDate = $activationDates[$i + 1];
            }
        }

        return $minDate->modify('+ ' . 60 * count($activationDates) . ' days')->format('Y-m-d H:i:s');
    }

    return $activationDates[0]->modify('+ 60 days')->format('Y-m-d H:i:s');
}
