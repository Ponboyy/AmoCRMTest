<?
require 'vendor/autoload.php';

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\Fields\ValueMultitextModel;
use AmoCRM\Models\Fields\ValueNumberModel;
use AmoCRM\Models\Fields\ValueTextModel;

$subdomain = 'testric'; 
$clientId = '0d086349-a6ef-496d-8ed4-14c8a21b18d8'; 
$clientSecret = 'RAdpow5NoNytQ12Liw3FolbGa0JsuKIHhSayZltZd82rC1f3xDENYUKTiEN6keNp'; 
$redirectUri = 'http://localhost'; 

$$apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);
$apiClient->setAccountBaseDomain($subdomain)
    ->onAccessTokenRefresh(
        function ($accessToken, $apiClient) {
            $memcached = new Memcached();
            $memcached->addServer('localhost', 11211);
            $memcached->set('access_token', $accessToken['access_token']);
        }
    )
    ->setAccessToken('your_access_token') // Access Token
    ->setAccessTokenExpires(time() + 86400)
    ->setRefreshToken('your_refresh_token'); //  Refresh Token

$apiClient->auth->authorize();

$data = [
    'name'  => $_POST['name'],
    'email' => $_POST['email'],
    'phone' => $_POST['phone'],
    'price' => $_POST['price'],
];

// Создаем сделку
$lead = new LeadModel();
$lead->setName('Заявка с сайта')
    ->setPrice((int)$data['price']);

// Создаем контакт
$contact = new ContactModel();
$contact->setName($data['name'])
    ->addCustomField((new ValueMultitextModel())->setFieldCode('EMAIL')->setValues([$data['email']]))
    ->addCustomField((new ValueMultitextModel())->setFieldCode('PHONE')->setValues([$data['phone']]));

$leadsCollection = new LeadsCollection();
$leadsCollection->add($lead);

$contactsCollection = new ContactsCollection();
$contactsCollection->add($contact);

$lead->setContacts($contactsCollection);

try {
    $apiClient->leads()->add($leadsCollection);
} catch (AmoCRMApiException $e) {
    print_r($e);
}

echo 'Заявка успешно создана в AmoCRM';