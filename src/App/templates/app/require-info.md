> __Input & Output data for all API__

> server_public_key = "*****"

__Input Data__
--------------

| Parameter    | Data Type   | Value     |
| -------------|-------------| ----------|
| **client_id**| String      | **game_code** |
| **app_key**  | String      | **RSA Encrypted** |
| **data**     | String      | **AES Encrypted** |

###### Encrypt app_key & data
```
 aes_secret_key = randomString(16);
    
 app_key = RSA_Encrypt(server_public_key, aes_secret_key);
 
 /**
  * params = encodeJSON(API Parameters)
  */
 data = AES_Encrypt(aes_secret_key, params);
```

__Output Data (JSON Data)__
---------------------------
```
{
    "code" : "ErrorCode", 
    "app_key": "RSA_Encrypted",
    "result": "AES_Encrypted"
}
```
###### Decrypt data (code=1)
```
 aes_secret = RSA_Decrypt(client_private_key, app_key);
 dataDecrypted = AES_Decrypt(aes_secret, result);
```
