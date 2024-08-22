<?php

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Models\CustomFieldsValues\NumericCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NumericCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\NumericCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use AmoCRM\Models\LeadModel;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Dotenv\Dotenv;

include_once '../vendor/autoload.php';

if (!isset($_GET['name']) || !isset($_GET['price']) || !isset($_GET['email']) || !isset($_GET['phone'])) {
    exit('INVALID REQUEST');
}

$dotenv = new Dotenv;
$dotenv->load('../.env');

$apiClient = new AmoCRMApiClient(
    $_ENV['CLIENT_ID'], $_ENV['CLIENT_SECRET'], $_ENV['CLIENT_REDIRECT_URI']
);

$apiClient->setAccountBaseDomain($_ENV['ACCOUNT_DOMAIN']);

$rawToken = json_decode(file_get_contents('../token.json'), 1);
$token = new AccessToken($rawToken);

$apiClient->setAccessToken($token);

$name = $_GET['name'];
$leadName = "Новая сделка $name";
$price = +$_GET['price'];
$email = $_GET['email'];
$phone = $_GET['phone'];


$lead = (new LeadModel)->setName("Новая сделка {$_GET['name']}")
    ->setPrice($price)->setCustomFieldsValues(
        (new CustomFieldsValuesCollection)->add(
                (new TextCustomFieldValuesModel)->setFieldId(
                        $_ENV['NAME_FIELD_ID']
                    )->setValues(
                        (new TextCustomFieldValueCollection)->add(
                                (new TextCustomFieldValueModel)->setValue(
                                    $name
                                )
                            )
                    )
            )->add(
                (new NumericCustomFieldValuesModel)->setFieldId(
                        $_ENV['PRICE_FIELD_ID']
                    )->setValues(
                        (new NumericCustomFieldValueCollection)->add(
                                (new NumericCustomFieldValueModel)->setValue(
                                    $price
                                )
                            )
                    )
            )->add(
                (new TextCustomFieldValuesModel)->setFieldId(
                    $_ENV['EMAIL_FIELD_ID']
                    )->setValues(
                        (new TextCustomFieldValueCollection)->add(
                            (new TextCustomFieldValueModel)->setValue(
                                $email
                                )
                            )
                        )
                )->add(
                (new TextCustomFieldValuesModel)->setFieldId(
                    $_ENV['PHONE_FIELD_ID']
                    )->setValues(
                        (new TextCustomFieldValueCollection)->add(
                            (new TextCustomFieldValueModel)->setValue(
                                $phone
                                )
                            )
                        )
                )->add(
            (new NumericCustomFieldValuesModel)->setFieldId($_ENV['SPENT_TIME_FIELD_ID'])->setValues(
                (new NumericCustomFieldValueCollection)->add(
                    (new NumericCustomFieldValueModel)->setValue($_GET['spent_time'])
                )
            )
        )
    );

$lead = $apiClient->leads()->addOne($lead);

echo "Заказ успешно отправлен. Id заказа: {$lead->getId()}";

