<?php

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Trading\Services;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class StoreController extends AppController {

    private $sandbox_creditials = array(
        'devId' => '63d91c83-63c5-48c8-b172-b55635290911',
        'appId' => 'JianLI-MiniElec-SBX-a8e2d25a0-c987eaef',
        'certId' => 'SBX-8e2d25a0b2be-6557-4b4e-aa07-4661'
    );

    /**
     * Helper function to print some information about the passed category.
     */
    private function printCategory($category, $level) {
        printf(
                "%s%s : (%s)<br/>", str_pad('', $level * 4), $category->Name, $category->CategoryID
        );
        foreach ($category->ChildCategory as $category) {
            printCategory($category, $level + 1);
        }
    }

    public function index() {
        /**
         * Create the service object.
         */
        $service = new Services\TradingService([
            'siteId' => Constants\SiteIds::FR
        ]);
        /**
         * Create the request object.
         */
        $request = new Types\GetStoreRequestType();
        /**
         * An user token is required when using the Trading service.
         *
         * NOTE: eBay will use the token to determine which store to return.
         */
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = getenv('EBAY_AuthToken');
        /**
         * Send the request.
         */
        $response = $service->getStore($request);
        /**
         * Output the result of calling the service operation.
         */
        if (isset($response->Errors)) {
            foreach ($response->Errors as $error) {
                printf(
                        "%s: %s<br/>%s<br/><br/>", $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning', $error->ShortMessage, $error->LongMessage
                );
            }
        }
        if ($response->Ack !== 'Failure') {
            $store = $response->Store;
            printf(
                    "Name: %s<br/>Description: %s<br/>URL: %s<br/><br/>", $store->Name, $store->Description, $store->URL
            );
            foreach ($store->CustomCategories->CustomCategory as $category) {
                $this->printCategory($category, 0);
            }
        }
    }

    public function sold() {
        $service = new Services\TradingService(array(
            'siteId' => Constants\SiteIds::FR
        ));
        /**
         * Create the request object.
         *
         * For more information about creating a request object, see:
         * http://devbay.net/sdk/guides/getting-started/#request-object
         */
        $request = new Types\GetMyeBaySellingRequestType();
        /**
         * An user token is required when using the Trading service.
         *
         * For more information about getting your user tokens, see:
         * http://devbay.net/sdk/guides/application-keys/
         */
        $args = array(
            "OrderStatus" => "Completed",
            "OrderStatus" => "All",
            "SortingOrder" => "Ascending",
            //"OrderRole"     => "Seller",
            "CreateTimeFrom" => new \DateTime('2017-08-19'),
            "CreateTimeTo" => new \DateTime('2017-08-21'),
        );

        $getOrders = new Types\GetOrdersRequestType($args);
        $getOrders->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $getOrders->RequesterCredentials->eBayAuthToken = getenv('EBAY_AuthToken');
        $getOrders->IncludeFinalValueFee = true;
        $getOrders->Pagination = new Types\PaginationType();
        $getOrders->Pagination->EntriesPerPage = 1000;
//$getOrders->OrderIDArray = new Types\OrderIDArrayType();
        $getOrdersPageNum = 1;

//$getOrders->OrderIDArray->OrderID[] = '200980916385-1185594371010'; //'200980916385-1185594371010'
        $response = $service->getOrders($getOrders);
        foreach ($response->OrderArray->Order as $order) {
            echo "Order ID :$order->OrderID<br/>";
            echo "Order statis:" . $order->OrderStatus . "<br/>";
            echo "Amount saved :$order->AmountSaved<br/>";
            echo "Amount Paid :$order->AmountPaid<br/>";
            echo "Buyer :$order->BuyerUserID<br/>";
            echo "Name:" . $order->ShippingAddress->Name . "<br/>";
            echo "City:" . $order->ShippingAddress->CityName . "<br/>";
            echo "Code Postale:" . $order->ShippingAddress->PostalCode . "<br/>";
            echo "Country:" . $order->ShippingAddress->County . "<br/>";
            echo "Country Name:" . $order->ShippingAddress->CountryName . "<br/>";
            echo "<br/><br/><br/>";
        }
        exit(0);
    }

    public function selling() {
        /**
         * Create the service object.
         */
        $service = new Services\TradingService(
                ['siteId' => Constants\SiteIds::US]
        );
        /**
         * Create the request object.
         */
        $request = new Types\GetMyeBaySellingRequestType();
        /**
         * An user token is required when using the Trading service.
         */
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = getenv('EBAY_AuthToken');
        /**
         * Request that eBay returns the list of actively selling items.
         * We want 10 items per page and they should be sorted in descending order by the current price.
         */
        $request->ActiveList = new Types\ItemListCustomizationType();
        $request->ActiveList->Include = true;
        
        $request->ActiveList->Pagination = new Types\PaginationType();
        $request->ActiveList->Pagination->EntriesPerPage = 10;
        $request->ActiveList->Sort = Enums\ItemSortTypeCodeType::C_CURRENT_PRICE_DESCENDING;
        $pageNum = 1;
        do {
            $request->ActiveList->Pagination->PageNumber = $pageNum;
            /**
             * Send the request.
             */
            $response = $service->getMyeBaySelling($request);
            /**
             * Output the result of calling the service operation.
             */
            echo "==================<br/>Results for page $pageNum<br/>==================<br/>";
            if (isset($response->Errors)) {
                foreach ($response->Errors as $error) {
                    printf(
                            "%s: %s<br/>%s<br/><br/>", $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning', $error->ShortMessage, $error->LongMessage
                    );
                }
            }
            if ($response->Ack !== 'Failure' && isset($response->ActiveList)) {
                foreach ($response->ActiveList->ItemArray->Item as $item) {
                    
                    printf(
                            
                            "(ID:%s) </br>Title : %s: </br>Currency: %s </br>Value:%.2f</br>Quantity:%s<br/>Quantity available:%s<br/>UUID: %s<br/>", 
                            $item->ItemID, $item->Title, $item->SellingStatus->CurrentPrice->currencyID, 
                            $item->SellingStatus->CurrentPrice->value, $item->Quantity, 
                            $item->QuantityAvailable, $item->UUID
                    );
                    if (isset($item->ItemSpecifics)) {
                        print("<br/>This item has the following item specifics:<br/><br/>");
                        foreach ($item->ItemSpecifics->NameValueList as $nameValues) {
                            printf(
                                "%s: %s<br/>",
                                $nameValues->Name,
                                implode(', ', iterator_to_array($nameValues->Value))
                            );
                        }
                    }
                    echo "Ship by :".$item->SellerProfiles->SellerShippingProfile->ShippingProfileName."<br/>";
                    echo "Shipping type :".$item->ShippingDetails->ShippingType."<br/>";
                    
                    echo "Galery:".$item->PictureDetails->GalleryURL.'<br/>';
                    
                    if (isset($item->Variations)) {
                        print("<br/>This item has the following variations:<br/>");
                        foreach( $item->Variations->Pictures as $picture){
                            echo "Picture specifics : $picture->VariationSpecificName";
                        }
                        
                        foreach ($item->Variations->Variation as $variation) {
                            
                            printf(
                                "<br/>SKU: %s<br/>Start Price: %s<br/>",
                                $variation->SKU,
                                $variation->StartPrice->value
                            );
                            printf(
                                "Quantity sold %s, quantiy available %s<br/>",
                                $variation->SellingStatus->QuantitySold,
                                $variation->Quantity - $variation->SellingStatus->QuantitySold
                            );
                            foreach ($variation->VariationSpecifics as $specific) {
                                foreach ($specific->NameValueList as $nameValues) {
                                    printf(
                                        "%s: %s<br/>",
                                        $nameValues->Name,
                                        implode(', ', iterator_to_array($nameValues->Value))
                                    );
                                }
                            }
                            echo "Variation Title : ".$variation->VariationTitle."<br/>";
                            echo "Variation URL : ".$variation->VariationViewItemURL."<br/>";
                        }
                    }
                    
                    
                    echo '<br/><br/>';
                }
            }
            $pageNum += 1;
        } while (isset($response->ActiveList) && $pageNum <= $response->ActiveList->PaginationResult->TotalNumberOfPages);
    }

    public function category() {
        $service = new Services\TradingService([
            'siteId' => Constants\SiteIds::FR
        ]);
        /**
         * Create the request object.
         */
        $request = new Types\GetCategoriesRequestType();
        /**
         * An user token is required when using the Trading service.
         */
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = getenv('EBAY_AuthToken');
        /**
         * By specifying 'ReturnAll' we are telling the API return the full category hierarchy.
         */
        $request->DetailLevel = ['ReturnAll'];
        /**
         * OutputSelector can be used to reduce the amount of data returned by the API.
         * http://developer.ebay.com/DevZone/XML/docs/Reference/ebay/GetCategories.html#Request.OutputSelector
         */
        $request->OutputSelector = [
            'CategoryArray.Category.CategoryID',
            'CategoryArray.Category.CategoryParentID',
            'CategoryArray.Category.CategoryLevel',
            'CategoryArray.Category.CategoryName'
        ];
        /**
         * Send the request.
         */
        $response = $service->getCategories($request);
        /**
         * Output the result of calling the service operation.
         */
        if (isset($response->Errors)) {
            foreach ($response->Errors as $error) {
                printf(
                        "%s: %s<br/>%s<br/><br/>", $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning', $error->ShortMessage, $error->LongMessage
                );
            }
        }
        if ($response->Ack !== 'Failure') {
            /**
             * For the US site this will output approximately 18,000 categories.
             */
            foreach ($response->CategoryArray->Category as $category) {

                printf(
                        "Level %s : %s (%s) : Parent ID %s<br/><br/>", $category->CategoryLevel, $category->CategoryName, $category->CategoryID, $category->CategoryParentID[0]
                );
            }
        }
    }

    private function buildItem() {
        /**
         * Begin creating the fixed price item.
         */
        $item = new Types\ItemType();
        /**
         * We want a multiple quantity fixed price listing.
         */
        $item->ListingType = Enums\ListingTypeCodeType::C_FIXED_PRICE_ITEM;
        $item->Quantity = 99;
        $item->ProductListingDetails = new Types\ProductListingDetailsType([
            'BrandMPN' => new Types\BrandMPNType([
                'Brand' => 'PIATO',
                'MPN' => '59602'])
        ]);
        /**
         * Let the listing be automatically renewed every 30 days until cancelled.
         */
        $item->ListingDuration = Enums\ListingDurationCodeType::C_GTC;
        /**
         * The cost of the item is $19.99.
         * Note that we don't have to specify a currency as eBay will use the site id
         * that we provided earlier to determine that it will be United States Dollars (USD).
         */
        $item->StartPrice = new Types\AmountType(['value' => 139.99]);
        /**
         * Allow buyers to submit a best offer.
         */
        $item->BestOfferDetails = new Types\BestOfferDetailsType();
        $item->BestOfferDetails->BestOfferEnabled = true;

        /**
         * Automatically accept best offers of $17.99 and decline offers lower than $15.99.
         */
        /* $item->ListingDetails = new Types\ListingDetailsType();
          $item->ListingDetails->BestOfferAutoAcceptPrice = new Types\AmountType(['value' => 17.99]);
          $item->ListingDetails->MinimumBestOfferPrice = new Types\AmountType(['value' => 15.99]); */

        /**
         * Provide a title and description and other information such as the item's location.
         * Note that any HTML in the title or description must be converted to HTML entities.
         */
        $item->Title = 'X Test produit API';
        $item->Description = '<h1>Bits & Bobs</h1><p>Just some &lt;stuff&gt; I found.</p>';
        $item->SKU = 'ABC-0010-0055';
        $item->Country = 'FR';
        $item->Location = 'Creteil';
        $item->PostalCode = '94000';
        /**
         * This is a required field.
         */
        $item->Currency = 'EUR';
        /**
         * Display a picture with the item.
         */
        $item->PictureDetails = new Types\PictureDetailsType();
        $item->PictureDetails->GalleryType = Enums\GalleryTypeCodeType::C_GALLERY;
        $item->PictureDetails->PictureURL = ['https://www.w3schools.com/css/img_fjords.jpg'];
        /**
         * List item in the Books > Audiobooks (29792) category.
         */
        $item->PrimaryCategory = new Types\CategoryType();
        $item->PrimaryCategory->CategoryID = '181876';

        /**
         * Tell buyers what condition the item is in.
         * For the category that we are listing in the value of 1000 is for Brand New.
         */
        $item->ConditionID = 1000;
        /**
         * Buyers can use one of two payment methods when purchasing the item.
         * Visa / Master Card
         * PayPal
         * The item will be dispatched within 1 business days once payment has cleared.
         * Note that you have to provide the PayPal account that the seller will use.
         * This is because a seller may have more than one PayPal account.
         */
        $item->PaymentMethods = [
            'VisaMC',
            'PayPal'
        ];
        $item->PayPalEmailAddress = 'sc2mimini@hotmail.fr';
        $item->DispatchTimeMax = 1;
        /**
         * Setting up the shipping details.
         * We will use a Flat shipping rate for both domestic and international.
         */
        $item->ShippingDetails = new Types\ShippingDetailsType();
        $item->ShippingDetails->ShippingType = Enums\ShippingTypeCodeType::C_FLAT;
        /**
         * Create our first domestic shipping option.
         * Offer the Economy Shipping (1-10 business days) service at $2.00 for the first item.
         * Additional items will be shipped at $1.00.
         */
        $shippingService = new Types\ShippingServiceOptionsType();
        $shippingService->ShippingServicePriority = 1;
        $shippingService->ShippingService = 'FR_Ecopli';
        $shippingService->ShippingServiceCost = new Types\AmountType(['value' => 2.00]);
        $shippingService->ShippingServiceAdditionalCost = new Types\AmountType(['value' => 1.00]);
        $item->ShippingDetails->ShippingServiceOptions[] = $shippingService;


        /**
         * Create our second international shipping option.
         * Offer the USPS Priority Mail International (6-10 business days) service at $5.00 for the first item.
         * Additional items will be shipped at $4.00.
         * The item will only be shipped to the following locations with this service.
         * N. and S. America
         * Canada
         * Australia
         * Europe
         * Japan
         */
        $shippingService = new Types\InternationalShippingServiceOptionsType();
        $shippingService->ShippingServicePriority = 2;
        $shippingService->ShippingService = 'FR_OtherInternational';
        $shippingService->ShippingServiceCost = new Types\AmountType(['value' => 5.00]);
        $shippingService->ShippingServiceAdditionalCost = new Types\AmountType(['value' => 4.00]);
        $shippingService->ShipToLocation = [
            'Europe',
            'IT'
        ];
        $item->ShippingDetails->InternationalShippingServiceOption[] = $shippingService;
        /**
         * The return policy.
         * Returns are accepted.
         * A refund will be given as money back.
         * The buyer will have 14 days in which to contact the seller after receiving the item.
         * The buyer will pay the return shipping cost.
         */
        $item->ReturnPolicy = new Types\ReturnPolicyType();
        $item->ReturnPolicy->ReturnsAcceptedOption = 'ReturnsAccepted';
        $item->ReturnPolicy->RefundOption = 'MoneyBack';
        $item->ReturnPolicy->ReturnsWithinOption = 'Days_14';
        $item->ReturnPolicy->ShippingCostPaidByOption = 'Buyer';


        return $item;
    }

    public function verify() {
        $siteId = Constants\SiteIds::FR;
        /**
         * Create the service object.
         */
        $service = new Services\TradingService([
            'credentials' => $this->sandbox_creditials,
            'sandbox' => true,
            'siteId' => $siteId,
            'debug' => [
                'logfn' => function ($msg) {
                    debug($msg);
                },
            ]
        ]);
        /**
         * Create the request object.
         */
        $request = new Types\AddFixedPriceItemRequestType();
        /**
         * An user token is required when using the Trading service.
         */
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = getenv('EBAY_Sandbox_AuthToken');

        /**
         * Finish the request object.
         */
        $request->Item = $this->buildItem();

        /**
         * Send the request.
         */
        $response = $service->verifyAddFixedPriceItem($request);
        if (isset($response->Errors)) {
            foreach ($response->Errors as $error) {
                printf(
                        "%s: %s<br/>%s<br/><br/>", $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning', $error->ShortMessage, $error->LongMessage
                );
            }
        }
        $verified = $response->Ack !== 'Failure';
        if ($verified) {
            print("This item was verified.<br/>");
        } else {
            print("This item was not verified.<br/>");
        }
    }
    
    public function revise() {
        $siteId = Constants\SiteIds::FR;
        $service = new Services\TradingService([
            'siteId' => $siteId
        ]);
        /**
         * Create the request object.
         */
        $request = new Types\ReviseFixedPriceItemRequestType();
        /**
         * An user token is required when using the Trading service.
         */
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = getenv('EBAY_AuthToken');
        /**
         * Begin creating the fixed price item.
         */
        $item = new Types\ItemType();
        /**
         * Tell eBay which item we are revising.
         */
        $item->ItemID = '282470815138';
        $item->Quantity = 99;
        $item->StartPrice = new Types\AmountType(['value' => 399.99]);
        /**
         * Remove some existing information.
         */
        $request->DeletedField[] = 'Item.SKU';
        /**
         * Change title to an audiobook of a well known novel.
         */
       ///// $item->Title = "Harry Potter and the Philosopher's Stone";
       ///// $item->Description = 'Audiobook of the wizard novel';
        /**
         * Change the category to Books > Audiobooks (29792) category.
         */
        
       ///// $item->PrimaryCategory = new Types\CategoryType();
       ///// $item->PrimaryCategory->CategoryID = '29792';
        
        /**
         * Item specifics describe the aspects of the item and are specified using a name-value pair system.
         * For example:
         *
         *  Color=Red
         *  Size=Small
         *  Gemstone=Amber
         *
         * The names and values that are available will depend upon the category the item is listed in.
         * Before specifying your item specifics you would normally call GetCategorySpecifics to get
         * a list of names and values that are recommended by eBay.
         * Showing how to do this is beyond the scope of this example but it can be assumed that
         * a call has previously been made and the following names and values were returned.
         *
         * Subject=Fiction & Literature
         * Topic=Fantasy
         * Format=MP3 CD
         * Length=Unabridged
         * Language=English
         *
         * In addition to the names and values that eBay has recommended this item will list with
         * its own custom item specifics.
         *
         * Bit rate=320 kbit/s
         * Narrated by=Stephen Fry
         *
         * Note that some categories allow multiple values to be specified for each name.
         * This example will only use one value per name.
         */
        $item->ItemSpecifics = new Types\NameValueListArrayType();
        $specific = new Types\NameValueListType();
        $specific->Name = 'Subject';
        $specific->Value[] = 'Fiction & Literature';
        $item->ItemSpecifics->NameValueList[] = $specific;
        /**
         * This shows an alternative way of adding a specific.
         */
        $item->ItemSpecifics->NameValueList[] = new Types\NameValueListType([
            'Name' => 'Topic',
            'Value' => ['Fantasy']
        ]);
        $specific = new Types\NameValueListType();
        $specific->Name = 'Format';
        $specific->Value[] = 'MP3 CD';
        $item->ItemSpecifics->NameValueList[] = $specific;
        $specific = new Types\NameValueListType();
        $specific->Name = 'Length';
        $specific->Value[] = 'Unabrided';
        $item->ItemSpecifics->NameValueList[] = $specific;
        $specific = new Types\NameValueListType();
        $specific->Name = 'Language';
        $specific->Value[] = 'English';
        $item->ItemSpecifics->NameValueList[] = $specific;
        /**
         * Add the two custom item specifics.
         * Notice they are no different to eBay recommended item specifics.
         */
        $specific = new Types\NameValueListType();
        $specific->Name = 'Bit rate';
        $specific->Value[] = '320 kbit/s';
        $item->ItemSpecifics->NameValueList[] = $specific;
        $specific = new Types\NameValueListType();
        $specific->Name = 'Narrated by';
        $specific->Value[] = 'Stephen Fry';
        $item->ItemSpecifics->NameValueList[] = $specific;
        /**
         * Finish the request object.
         */
        $request->Item = $item;
        /**
         * Send the request.
         */
        $response = $service->reviseFixedPriceItem($request);
        /**
         * Output the result of calling the service operation.
         */
        if (isset($response->Errors)) {
            foreach ($response->Errors as $error) {
                printf(
                        "%s: %s<br/>%s<br/><br/>", $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning', $error->ShortMessage, $error->LongMessage
                );
            }
        }
        if ($response->Ack !== 'Failure') {
            print("The item was successfully revised on the eBay Sandbox.");
        }
    }

    public function create() {
        $siteId = Constants\SiteIds::FR;
        /**
         * Create the service object.
         */
        $service = new Services\TradingService([
           // 'credentials' => $this->sandbox_creditials,
            //'sandbox' => true,
            'siteId' => $siteId,
            'debug' => [
                'logfn' => function ($msg) {
                    debug($msg);
                },
            ]
        ]);
        /**
         * Create the request object.
         */
        $request = new Types\AddFixedPriceItemRequestType();
        /**
         * An user token is required when using the Trading service.
         */
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = getenv('EBAY_AuthToken');

        /**
         * Finish the request object.
         */
        $request->Item = $this->buildItem();

        /**
         * Send the request.
         */
        $response = $service->addFixedPriceItem($request);
        /**
         * Output the result of calling the service operation.
         */
        if (isset($response->Errors)) {
            foreach ($response->Errors as $error) {
                printf(
                        "%s: %s<br/>%s<br/><br/><br/>", $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning', $error->ShortMessage, $error->LongMessage
                );
            }
        }
        if ($response->Ack !== 'Failure') {
            printf(
                    "The item was listed to the eBay Sandbox with the Item number %s<br/>", $response->ItemID
            );
        }
    }
    
    public function inventory(){
        
        
    }
    
}
