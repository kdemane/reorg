<?php
// GET https://openpaymentsdata.cms.gov/resource/s4av-yhxs.json

ini_set('memory_limit', '500M');

$curl = curl_init();

//$url = sprintf("%s?%s", $url, http_build_query(FALSE));

curl_setopt($curl, CURLOPT_URL, 'https://openpaymentsdata.cms.gov/resource/s4av-yhxs.json');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

$result = curl_exec($curl);

curl_close($curl);

$data = json_decode($result);

print_r($data);
print "\n";

?>