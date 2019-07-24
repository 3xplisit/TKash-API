# Telkom-Kenya-API
T-Kash API. Not the Official SDK but the API will give insights to new and upcoming developers who seek to integrate T-Kash with their existing systems. The Raw PHP Code seeks to target upcoming developers new to the language and to offer insights on the various endpoints. Laravel and more advanced SDK will be launching soon.

How to call the file.
Instantiate and instance of the TKASHController

$controller = new TKASHController;
//Make api calls utilizing the functions created
$controller->RegisterURL('https://<your confirmationURL>','<Your Validation URL>',100));; //This Function call accepts three parameters (1. Validation URL. 2. Confirmation URL 3. Numeric Value either 100 or 200 (100 being referrence for Response with headers only and 200 for json encoded responses.)
