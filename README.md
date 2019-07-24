# Telkom-Kenya-API
The Sample Controller file given conforms to the T-Kash API Version 1.4.1.

Not the Official SDK but the API will give insights to those who seek to integrate T-Kash with their existing systems. 

How to call the file.
Instantiate and instance of the TKASHController

$call = new TKASHController;

$call->RegisterURL("https://xyz.com/confirmation","https://xyz.com/validation",100);
  
