<?php

#  conf :: (required) : namespace and utilities
#  --------------------------------------------------------------------------------------------------------------------------------------------
    namespace Realmdigital\Web\Controller;

    use DDesrosiers\SilexAnnotations\Annotations as SLX;
    use Silex\Application;

    require_once('./utils.php');
#  --------------------------------------------------------------------------------------------------------------------------------------------




#  ctrl :: ProductController : handle products
#  --------------------------------------------------------------------------------------------------------------------------------------------
    /**
     * @SLX\Controller(prefix="product/")
     */
    class ProductController
    {
    #  func :: getByProp_GET : get external data by property reference
    #  ----------------------------------------------------------------------------------------------------------------------------------------
        /**
         * @SLX\Route(
         *      @SLX\Request(method="GET", uri="/{prop}")    // NOTE :: haven't worked with `Silex` so if it does not support this, my bad
         * )
         * @param Application $app
         * @param $prop
         * @return
         */
        public function getByProp_GET(Application $app, $prop)
        {
        #  cond :: args : internal - throw exception if arguments are invalid - `$prop` must be an associative array
        #  ------------------------------------------------------------------------------------------------------------------------------------
            if (!is_assoc_array($prop))
            { throw new Exception(__CLASS__.' :: '.__FUNCTION__.' : expecting arguments ([class_instance, assoc_array])'); }
        #  ------------------------------------------------------------------------------------------------------------------------------------



        #  conf :: vars : local
        #  ------------------------------------------------------------------------------------------------------------------------------------
            $prot = # result record-item ptototype - for function-result-consistency and replacing missing fields in result data records
            [
                'barcode'=>null,
                'itemName'=>null,
                'prices'=>
                [
                    ['currencyCode'=>null, 'sellingPrice'=>null]
                ]
            ];

            $tmpl = (array_key_exists('id',$prop) ? 'products/product.detail.twig' : 'products/products.twig'); # not sure if this is required
            $resl = []; # result data - numeric-array of associative-array-items
        #  ------------------------------------------------------------------------------------------------------------------------------------



        #  exec :: cURL : post reguest with `$prop` as `post-fields`
        #  ------------------------------------------------------------------------------------------------------------------------------------
            $curl = curl_init();

            curl_setopt_array($curl,
            [
                CURLOPT_URL => 'http://192.168.0.241/eanlist?type=Web',
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $prop,
                CURLOPT_RETURNTRANSFER => 1,
            ]);

            $resp = curl_exec($curl);
            curl_close($curl);
        #  ------------------------------------------------------------------------------------------------------------------------------------



        #  cond :: cURL : external - if response is invalid JSON - do not throw exception - return *expected data* for API stability
        #  ------------------------------------------------------------------------------------------------------------------------------------
            $resp = (!$resp ? null : json_decode($resp));   # `$resp` is now either an array -or- `null`
            if ($resp === null){ return [$prot]; }          # return expected data-type
        #  ------------------------------------------------------------------------------------------------------------------------------------



        #  each :: resp : item - convert to expected record-set
        #  ------------------------------------------------------------------------------------------------------------------------------------
            foreach ($resp as $indx => $item)
            {
            #  cond :: `$item` : skip invalid items; improvise for missing fields
            #  --------------------------------------------------------------------------------------------------------------------------------
                if (!is_assoc_array($item)){ continue; }    # skipped
                $item = array_improv_proto($item,$prot);    # improvised
            #  --------------------------------------------------------------------------------------------------------------------------------


            #  each :: (item->prices) : cond - change if `currencyCode` is not "ZAR"
            #  --------------------------------------------------------------------------------------------------------------------------------
                foreach ($item['prices'] as $pidx => $pitm)
                {
                    if ($pitm['currencyCode'] != 'ZAR')
                    {
                        $item['prices'][$pidx] = // array
                        [
                            'price'     => $pitm['sellingPrice'],
                            'currency'  => $pitm['currencyCode'],   # "curreny" in original code .. typo ?
                        ];
                    }
                }
            #  --------------------------------------------------------------------------------------------------------------------------------


            #  edit :: `$resl` : append product record
            #  --------------------------------------------------------------------------------------------------------------------------------
                $resl[] = // array
                [
                    'ean'   => $item['barcode'],
                    'name'  => $item['itemName'],
                    'prices'=> $item['prices'],
                ];
            #  --------------------------------------------------------------------------------------------------------------------------------
            }
        #  ------------------------------------------------------------------------------------------------------------------------------------



        #  done :: `$resl` : return rendered data using relevant `twig` template
        #  ------------------------------------------------------------------------------------------------------------------------------------
            return $app->render($tmpl, $resl);
        #  ------------------------------------------------------------------------------------------------------------------------------------
        }
    #  ----------------------------------------------------------------------------------------------------------------------------------------
    }
#  --------------------------------------------------------------------------------------------------------------------------------------------

?>
