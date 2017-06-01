
API Passport
============

## I. Login

> URL: [https://{domain}/api/login](https://{domain}/api/login)

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| username      | String     | true     |**/^[a-z0-9]{6,24}$/**|
| password      | String     | true     |**/[\s\S]{6,32}/**|
| source        | String     | false    |**/^([A-Z0-9-]{1,25})$/**|
| agent         | String     | false    |**/^[A-Z0-9-]{0,30}$/**|   

###### Example encrypt parameters
``` 
 params = {
    "username": "abc123",
    "password": "123456",
    "source": "568E",
    "agent": "abc"
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```
###### Result Data
```
 dataDecrypted = {
    "token": "jQidasggfDSFGerGHE",
    "uid": 123213,
    "username": "utest1",
    "email": "abc@gmail.com"
 }
```
___________________________________________________________

## II. Register

> URL: [https://{domain}/api/register](https://{domain}/api/register)

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| username      | String     | true     |**/^[a-z0-9]{6,24}$/**|
| password      | String     | true     |**/[\s\S]{6,32}/**|
| source        | String     | false    |**/^([A-Z0-9-]{1,25})$/**|
| agent         | String     | false    |**/^[A-Z0-9-]{0,30}$/**|
| email         | String     | false    | |

###### Example encrypt parameters
``` 
 params = {
    "username": "abc123",
    "password": "123456",
    "source": "568E",
    "agent": "abc"
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```
###### Result Data
```
 dataDecrypted = {
    "token": "jQidasggfDSFGerGHE",
    "uid": 123213,
    "username": "utest1",
    "email": "abc@gmail.com"
 }
```
___________________________________________________________

## III. Login Social

> URL: [https://{domain}/api/oauth](https://{domain}/api/oauth)

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| oauth_id      | String     | true     |**/[\s\S]/**|
| client        | String     | true     |[**facebook, google, twitter**]|
| source        | String     | false    |**/^([A-Z0-9-]{1,25})$/**|
| agent         | String     | false    |**/^[A-Z0-9-]{0,30}$/**|   

###### Example encrypt parameters
``` 
 params = {
    "oauth_id": "abc123sdfsaf",
    "client": "facebook",
    "source": "568E",
    "agent": "abc"
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```
###### Result Data
```
 dataDecrypted = {
    "token": "jQidasggfDSFGerGHE",
    "uid": 123213,
    "username": "utest1"
 }
```


## IV. Link Social Account TODO

> URL: [https://{domain}/api/link-oauth](https://{domain}/api/link-oauth)

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| username      | String     | true     |**/^[a-z0-9]{6,24}$/**|
| oauth_id      | String     | true     |**/[\s\S]/**|
| client        | String     | true     |[**facebook, google, twitter**]|
| source        | String     | false    |**/^([A-Z0-9-]{1,25})$/**|
| agent         | String     | false    |**/^[A-Z0-9-]{0,30}$/**|   

###### Example encrypt parameters
``` 
 params = {
    "username": "abc123",
    "oauth_id": "abc123sdfsaf",
    "client": "facebook",
    "source": "568E",
    "agent": "abc"
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```
###### Result Data
```
 {
    "code" : "ErrorCode", 
 }
```
___________________________________________________________

## V. Change Password

> URL: [https://{domain}/api/change-pass](https://{domain}/api/change-pass)

> Headers: Authorization: Bearer {token_string_here}

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| username      | String     | true     |**/^[a-z0-9]{6,24}$/**|
| oldPassword   | String     | true     |**/[\s\S]{6,32}/**|
| newPassword   | String     | true     |**/[\s\S]{6,32}/**|

###### Example encrypt parameters
``` 
 params = {
    "username": "abc123",
    "oldPassword": "123456",
    "newPassword": "1234565467"
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```
__Output Data (JSON Data)__
---------------------------
```
{
    "code" : "ErrorCode", 
}
```

___________________________________________________________

## VI. Forgot Password

> URL: [https://{domain}/api/forgot-pass](https://{domain}/api/forgot-pass)

> Headers: Authorization: Bearer {token_string_here}

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| username      | String     | true     |**/^[a-z0-9]{6,24}$/**|


###### Example encrypt parameters
``` 
 params = {
    "username": "abc123"
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```
__Output Data (JSON Data)__
---------------------------
```
 dataDecrypted = {
    "email": "abc@gmail.com",
    "code": "12323DSFDGS"
 }
```

___________________________________________________________

## VII. Reset Password

> URL: [https://{domain}/api/reset-pass](https://{domain}/api/reset-pass)

> Headers: Authorization: Bearer {token_string_here}

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| username      | String     | true     |**/^[a-z0-9]{6,24}$/**|
| password      | String     | true     |**/[\s\S]{6,32}/**|
| code          | String     | true     ||


###### Example encrypt parameters
``` 
 params = {
    "username": "abc123",
    "password": "plaintext",
    "code": "12323DSFDGS"
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```
__Output Data (JSON Data)__
---------------------------
```
 {
    "code" : "ErrorCode", 
 }
```
___________________________________________________________

## VIII. Insert/Update Email

> URL: [https://{domain}/api/update-email](https://{domain}/api/update-email)

> Headers: Authorization: Bearer {token_string_here}

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| username      | String     | true     |**/^[a-z0-9]{6,24}$/**  |
| email         | String     | true     |Email  |

###### Example encrypt parameters
``` 
 params = {
    "username": "abc123",
    "email": "abc@gmail.com",
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```
__Output Data (JSON Data)__
---------------------------
```
{
    "code" : "ErrorCode", 
}
```

___________________________________________________________

## IX. Get Email

> URL: [https://{domain}/api/get-email](https://{domain}/api/get-email)

> Headers: Authorization: Bearer {token_string_here}

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| username      | String     | true     |**/^[a-z0-9]{6,24}$/**  |

###### Example encrypt parameters
``` 
 params = {
    "username": "abc123"
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```
__Output Data (JSON Data)__
---------------------------
```
 dataDecrypted = {
    "email": "abc@gmail.com"
 }
```
___________________________________________________________

## VI. Insert Mobile

> URL: [https://{domain}/api/update-mobile](https://{domain}/api/update-mobile)

> Headers: Authorization: Bearer {token_string_here}

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| username      | String     | true     |**/^[a-z0-9]{6,24}$/**  |
| mobile        | String     | true     |**/^[d]{0,13}$/**  |

###### Example encrypt parameters
``` 
 params = {
    "username": "abc123",
    "mobile": "0987654321",
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```
__Output Data (JSON Data)__
---------------------------
```
{
    "code" : "ErrorCode", 
}
```
___________________________________________________________

## VII. Insert CMND

> URL: [https://{domain}/api/update-identity-number](https://{domain}/api/update-identity-number)

> Headers: Authorization: Bearer {token_string_here}

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| identityNumber__identity_number        | String     | true     |**/^[d]{0,12}$/**  |
| identityNumber__date        | String     | true     |**dd-mm-yyyy**  |
| identityNumber__address       | String     | true     |**/^.{0,50}$/**  |

###### Example encrypt parameters
``` 
 params = {
    "identityNumber__identity_number": "012345789",
    "identityNumber__date": "12-12-2000",
    "identityNumber__address": "Ha Noi"
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```
__Output Data (JSON Data)__
---------------------------
```
{
    "code" : "ErrorCode", 
}
```
___________________________________________________________
