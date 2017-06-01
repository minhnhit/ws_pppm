
API Payment
============

## I. Charge Card

> URL: [https://{domain}/api/charge](https://{domain}/api/charge)

> Headers: Authorization: Bearer {token_string_here}

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| username      | String     | true     |**/^[a-z0-9]{6,24}$/**|
| cardNumber    | String     | true     |**/^[A-Z0-9]{9,16}$/**|
| cardSerial    | String     | true     |**/^[A-Z0-9]{8,16}$/**|
| cardType      | String     | true     |**/^[A-Z]{2,10}$/** <br/> ["VINA", "MOBI", "VT"]|
| ctype         | String     | true     |**"gold"** OR **"silver"** <br/> Default: silver. <br/> *Note:* "gold" => cashout   |
<!---
| amount        | Integer    | false    |**/^[d]{1,10}$/**|
| serverId      | String     | false    |**/^[a-zA-Z0-9-_]{1,15}$/**|
--->
###### Example encrypt parameters
``` 
 params = {
    "username": "test123",
    "cardNumber": "12324235456",
    "cardSerial": "12341231256",
    "cardType": "VINA",
    "ctype": "silver"
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```
###### Result Data
```
 dataDecrypted = {
    "transactionId": "jQidasggfDSFGerGHE",
    "amount": 123213,
    "gold": 100,
    "msg": "success"
 }
```
___________________________________________________________

## II. Exchange

> URL: [https://{domain}/api/exchange](https://{domain}/api/exchange)

> Headers: Authorization: Bearer {token_string_here}

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| passportId    | Integer    | true     |**/^[d]$/**|
| amount        | Integer    | true     |**/^[d]{1,10}$/**|
| serverId      | String     | true     |**/^[a-zA-Z0-9-_]{1,15}$/**|

###### Example encrypt parameters
``` 
 params = {
    "passportId": 12345,
    "amount": "100",
    "serverId": "S1"
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```
###### Result Data
```
 dataDecrypted = {
    "orderId": "jQidasggfDSFGerGHE",
    "uid": 123213,
    "username": "utest1",
    "amount": "100",
    "msg": "success"
 }
```
___________________________________________________________

## III. Get Balance

> URL: [https://{domain}/api/get-balance](https://{domain}/api/get-balance)

> Headers: Authorization: Bearer {token_string_here}

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| username      | String     | true     |**/^[a-z0-9]{6,24}$/**|

###### Example encrypt parameters
``` 
 params = {
    "username": "test123"
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```

###### Result Data
```
 dataDecrypted = {
    "balance" => {"gold": 12321, "point": 123, "silver": 123}
 }
```
___________________________________________________________

## IV. Update Match

> URL: [https://{domain}/api/update-match](https://{domain}/api/update-match)

> Headers: Authorization: Bearer {token_string_here}

> Method: __HTTP POST__

###### Parameters
| Param              | Data Type  | Required | Value      |
| ------------------ | ---------- | -------- | ---------- |
| winner_username    | String     | true     |**/^[a-z0-9]{6,24}$/**|
| loser_username     | String     | true     |**/^[a-z0-9]{6,24}$/**|
| matchId            | mixed      | true     | **/^[d]$/** |
| gold               | Integer    | true     | **/^[d]$/**|

###### Example encrypt parameters
``` 
 params = {
    "winner_username": "winner1",
    "loser_username": "loser1",
    "gold": 100
    "match_id": 12321213
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```

###### Result Data
```
 dataDecrypted = {
    "winner_username": "winner1",
    "winner_balance": {"gold": 12321, "point": 123, "silver": 123},
    "loser_username": "loser1",
    "loser_balance": {"gold": 12321, "point": 123, "silver": 123}
 }
```
___________________________________________________________

## V. Buy Card

> URL: [https://{domain}/api/buy-card](https://{domain}/api/buy-card)

> Headers: Authorization: Bearer {token_string_here}

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| username      | String     | true     |**/^[a-z0-9]{6,24}$/**|
| cardValue     | Integer    | true     |**/^[\d]/** |
| cardType      | String     | true     |**/^[A-Z]{2,10}$/** |

###### Example encrypt parameters
``` 
 params = {
    "username": "username1",
    "cardType": "VT",
    "cardValue": 100000
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```

###### Result Data
```
 dataDecrypted = {
   "card_pin": "12321313213213",
   "card_type": "VT",
   "card_serial": "234544657",
   "expired_date": "31-12-2018",
   "balance": {"gold": 12321, "point": 123, "silver": 123}
 }
```
___________________________________________________________

## VI. Insert Promotion

> URL: [https://{domain}/api/promotion](https://{domain}/api/promotion)

> Headers: Authorization: Bearer {token_string_here}

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| username      | Integer    | true     |**/^[a-z0-9]{6,24}$/**|
| promotionId   | Integer    | true     |**/^[d]$/**|
| promotionCode | String     | true     |**/^[\s\S]$/** |
| gold          | Integer    | true     |**/^[d]$/**|

###### Example encrypt parameters
``` 
 params = {
    "username": "username1",
    "promotionId": 123122131,
    "promotionCode": "DSF123",
    "gold": 100
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```

###### Result Data
```
 dataDecrypted = {
    "balance" => {"gold": 12321, "point": 123, "silver": 123}
 }
```
___________________________________________________________

## VII. Recheck Transaction

> URL: [https://{domain}/api/recheck](https://{domain}/api/recheck)

> Method: __HTTP POST__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| transactionId | String     | true     | |
| ctype         | String     | true     | **"gold"** OR **"silver"** <br/> Default: silver. <br/> *Note:* "gold" => cashout   |

###### Example encrypt parameters
``` 
 params = {
    "transactionId": "sdfsgsdg45346",
    "ctype": gold,
 };
 
 // @see: Input Data
 data = AES_Encrypt(aes_secret_key, params);
```

###### Result Data
```
 dataDecrypted = {
    "transactionId": "sdfsgsdg45346",
    "balance" => {"gold": 12321, "point": 123, "silver": 123},
    "amount": 10000
 }
```


## VIII. Get Config

> URL: [https://{domain}/payment/get-config](https://{domain}/payment/get-config)

> Method: __HTTP GET__

###### Parameters
| Param         | Data Type  | Required | Value |
| ------------- | ---------- | -------- | ----- |
| client_id     | String     | true     |c1|


###### Result Data
```
 {
    "cardList" => {0: "VINA", 1: "MOBI", 2: "VT"}
 }
```