<?php
ini_set('memory_limit', '500M');
require 'application/db/db.php';
require 'application/classes/drug_or_biological.php';
require 'application/classes/hospital.php';
require 'application/classes/manufacturer_or_GPO.php';
require 'application/classes/medical_supply.php';
require 'application/classes/payment.php';
require 'application/classes/physician.php';
require 'application/classes/recipient.php';
require 'application/classes/third_party.php';

$curl = curl_init();

curl_setopt($curl, CURLOPT_URL, 'https://openpaymentsdata.cms.gov/resource/s4av-yhxs.json');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

$result = curl_exec($curl);

curl_close($curl);

$data = json_decode($result);

$d  = new drug_or_biological($db);
$h  = new hospital($db);
$m  = new manufacturer_or_GPO($db);
$ms = new medical_supply($db);
$p  = new payment($db);
$ph = new physician($db);
$r  = new recipient($db);
$t  = new third_party($db);

// set up reference tables
foreach ($data as $payment)
{
    if ($d->extract($payment))
        $d->save();

    if ($h->extract($payment))
        $h->save();

    if ($m->extract($payment))
        $m->save();

    if ($ms->extract($payment))
        $ms->save();

    if ($ph->extract($payment))
        $ph->save();

    if ($t->extract($payment))
        $t->save();
}

// record payments with proper integrity
foreach ($data as $payment)
{
    if ($p->extract($payment))
        $p->save();
}
?>