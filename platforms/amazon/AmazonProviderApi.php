<?php

require_once(plugin_dir_path(__FILE__) . '../../util/isbn.php');

# http://webservices.amazon.de/onca/xml?AWSAccessKeyId=AKIAIXLSBMDYE34PWSWA&AssociateTag=787279780545&Condition=All&IdType=ISBN&ItemId=3570303284&Operation=ItemLookup&ResponseGroup=ItemAttributes&SearchIndex=Books&Service=AWSECommerceService&Timestamp=2014-10-25T20%3A11%3A03.000Z&Version=2011-08-01&Signature=Rj4CfBf0ZeZC%2B5mbr82zLImx1RfXc4qqvHrUBSIsykM%3D
# http://webservices.amazon.de/onca/xml?AWSAccessKeyId=AKIAIXLSBMDYE34PWSWA&AssociateTag=787279780545&Condition=All&IdType=ISBN&ItemId=3570303284&Operation=ItemLookup&ResponseGroup=ItemAttributes&SearchIndex=Books&Service=AWSECommerceService&Timestamp=2014-11-01T17%3A00%3A59.000Z&Version=2011-08-01&Signature=KE7JfluU4aKw9OYtnyHl7h13cJiG9PAGU42ZMzCqzKA%3D
class AmazonProviderApi
{
    const SQ_AMAZON_API_SECRET_KEY_OPTION = "sq_amazon_api_secret";
    const QUERY_STRING_TEMPLATE = 'AWSAccessKeyId=AKIAIXLSBMDYE34PWSWA&AssociateTag=787279780545&Condition=All&IdType=ISBN&ItemId=${ISBN10}&Operation=ItemLookup&ResponseGroup=ItemAttributes%2COffers&SearchIndex=Books&Service=AWSECommerceService&Timestamp=${TS}&Version=2011-08-01';

    private $host;
    private $urlpath;
    private $secrete;

    public function __construct($pHost, $pUrlpath) {
        $this->host = $pHost;
        $this->urlpath = $pUrlpath;
        $this->secrete = get_option(AmazonProviderApi::SQ_AMAZON_API_SECRET_KEY_OPTION);
    }

    public function urlBy($isbn)
    {
        if ($isbn==null) return null;

        $queryString = str_replace("\${ISBN10}", Isbn::to10($isbn), AmazonProviderApi::QUERY_STRING_TEMPLATE);

        $timeStamp = urlencode($this->getTimestamp());
        $queryString = str_replace("\${TS}", $timeStamp, $queryString);

        // echo "QS = " . $queryString . "\n\n";

        $queryStringForSigning = $this->amazonRequestForSigning($queryString);
        // echo "QS4Signing = " . $queryStringForSigning . "\n\n";

        $url = $this->url($queryString , "Signature=". $this->signatureFor($queryStringForSigning));
        // echo "URL = " . $url  . "\n\n";

        return $url;
    }


    public function signatureFor($request)
    {
        return urlencode(base64_encode(hash_hmac("sha256", $request, $this->secrete, true)));
    }

    private function getTimestamp()
    {
        return gmdate("Y-m-d\TH:i:s\Z");
    }

    private function amazonRequestForSigning ($queryString)
    {
        return "GET\n" . $this->host . "\n" . $this->urlpath . "\n" . $queryString;
    }

    private function url ($queryString, $signature)
    {
        return 'http://' . $this->host . '/' . $this->urlpath . '?' . $queryString . '&' . $signature;
    }

}