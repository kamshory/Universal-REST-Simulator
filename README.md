

# Universal REST Simulator

## Introduction

Universal REST Simulator is a universal simulator for REST APIs. This simulator can be configured by adding some configuration files into the `config` directory. Universal REST Simulator can read all file extensions.

The configuration is similar to the `this` file. If the configuration value is more than one line, then the value in the line other than the last must end with `\` (backslash).

The tutorial can be read at https://github.com/kamshory/Universal-REST-Simulator/blob/main/tutorial.md

Example:

```ini
PARSING_RULE=\
$INPUT.PRODUCT=$REQUEST.product_code\
$INPUT.ACCOUNT=$REQUEST.customer_no\
$INPUT.REF_NUMBER=$REQUEST.refno\
$INPUT.ACCEPT_LANGUAGE=$OUTPUT.HEADER.ACCEPT_LANGUAGE\
$INPUT.AMOUNT=$REQUEST.amount
```

The `PARSING_RULE` property above consists of 4 lines. If the `\` character is omitted, then `PARSING_RULE` has no value. If the `\` in line 2 is omitted it becomes as follows:

```ini
PARSING_RULE=\
$INPUT.PRODUCT=$REQUEST.product_code\
$INPUT.ACCOUNT=$REQUEST.customer_no
$INPUT.REF_NUMBER=$REQUEST.refno\
$INPUT.AMOUNT=$REQUEST.amount
```

then `PARSING_RULE` only consists of 2 lines, namely:

```ini
$INPUT.PRODUCT=$REQUEST.product_code\
$INPUT.ACCOUNT=$REQUEST.customer_no
```

Because after the line loses `\` at the end, the simulator will not continue reading the data. This configuration method applies to all properties.

## Path

**Property: `PATH`**

Universal REST Simulator will select the configuration according to the `path` which is accessed by the same method as the request method. For example: user creates 7 configuration files, Universal REST Simulator will select one file with appropriate `path`. After getting a file with the appropriate path and method, the simulator will stop looking for other files.

Example configuration file structure:

```
[root]
    [config]
        config1.txt
        config2.txt
        config3.txt
```

`root` is the document root of the simulator
`config` is a directory under document root
The files `config1.txt`, `config2.txt`, `config3.txt` are in the `config` directory.

If the `PATH` in the `config1.txt` file is `/biller/config1`, then the file will be selected only if the path in the request URL is `/biller/config1`. If the `PATH` in the `config2.txt` file is `/bank/config2`, then the file will be selected only if the path in the request URL is `/bank/config2`. If none of the files with `path` match, then the simulator will give no response.

## Method

**Property: `METHOD`**

User can choose `GET`, `POST` or `PUT` method. Universal REST Simulator will read the request according to the method used in the configuration and will ignore other requests.

## Content Type

### Content Type Request

**Property: `REQUEST_TYPE`**

Universal REST Simulator supports 3 kinds of `content types` which are as follows:

1. application/x-www-form-urlencoded
2. application/json
3. application/xml
4. application/soap+xml

This `content type` will affect how to read requests on the simulator.

### Content Type Response

**Property: `RESPONSE_TYPE`**

Users are free to use any content type for the response because basically the response simulator is pure text.

## Input Configuration

**Property: `PARSING_RULE`**

`$INPUT` is an object that can be thought of as a global variable and has properties. `$INPUT` is always capitalized. The property of `$INPUT` can be written in both uppercase and lowercase letters and will be _case sensitive_.

Input comes from 2 sources namely `$REQUEST` (_request body_ in `POST` and `PUT` and _query string_ in `GET`) and `$OUTPUT.HEADER` (request header). Both `$REQUEST` and `$OUTPUT.HEADER` must be capitalized. The property name of `$REQUEST` is _case sensitive_ while the property name of `$OUTPUT.HEADER` is capitalized and `-` is changed to `_`. This is because the header properties may have changed and cannot be predicted with certainty.

`$REQUEST` can come from:
1. Query on `GET`
2. Request body on `POST` and `PUT`
3. Wildcard URL
4. Basic Authorization

To retrieve `$REQUEST` from a URL wildcard, simply use `{[IDENTIFIER]}` in the URL. `{[IDENTIFIER]}` is _case sensitive_.

Example:

```ini
PATH=/payment/{[PRODUCT_CODE]}/{[CUSTOMER_ID]}/{[REFERENCE_NUMBER]}
```

If the client requests either `GET`, `POST` or `PUT` with URL `/payment/123/456/7890`, then `$REQUEST.PRODUCT_CODE` will be `123`, `$REQUEST.CUSTOMER_ID` will be value `456`, `$REQUEST.REFERENCE_NUMBER` will be `7890`. URL wildcards can still be concatenated with query strings. Both the input from the URL wildcard and the query string can be parsed in a single request.

Basic authorization contains a username and password to access a data source. The username and password information is encoded with base 64. The simulator extracts the information and then saves it to the `$AUTHORIZATION_BASIC` object.

To retrieve the username from basic authorization, use `$AUTHORIZATION_BASIC.USERNAME`. To retrieve the password from basic authorization, use `$AUTHORIZATION_BASIC.PASSWORD`. `$AUTHORIZATION_BASIC.USERNAME` and `$AUTHORIZATION_BASIC.PASSWORD` must be capitalized.

The simulator reads the input depending on the `content type` request. For `content type` `application/x-www-form-urlencoded`, the simulator directly fetches the value of the appropriate parameter. For `application/json` and `application/xml` content types, the simulator will fetch data incrementally. Thus, users are free to provide JSON and XML requests with a nested structure.

The input matrix and methods of Universal REST Simulator are as follows:

| Method | Content Tpe                         | Data Source  | Alternative Object              |
| ------ | ----------------------------------- | ------------ | --------------------- |
| `GET`  | `applicatiom/x-www-form-urlencoded` | Header, URL, <br>Basic Authorization, <br>GET  | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET` |
| `POST` | `applicatiom/x-www-form-urlencoded` | Header, Body, <br>Basic Authorization, <br>GET, POST | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET`, `$POST` |
| `POST` | `applicatiom/json`                  | Header, Body, <br>Basic Authorization, <br>GET | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET` |
| `POST` | `applicatiom/xml`                   | Header, Body, <br>Basic Authorization, <br>GET | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET` |
| `POST` | `applicatiom/soap+xml`              | Header, Body, <br>Basic Authorization, <br>GET | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET` |
| `PUT`  | `applicatiom/x-www-form-urlencoded` | Header, Body, <br>Basic Authorization, <br>GET, PUT | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET`, `$PUT` |
| `PUT`  | `applicatiom/json`                  | Header, Body, <br>Basic Authorization, <br>GET | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET` |
| `PUT`  | `applicatiom/xml`                   | Header, Body, <br>Basic Authorization, <br>GET | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET` |
| `PUT`  | `applicatiom/soap+xml`              | Header, Body, <br>Basic Authorization, <br>GET | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET` |

**Input from Objects and Arrays**

Users may use a combination of `array` and `object` as the `payload` of the `request` either `GET`, `POST`, `PUT`, or `REQUEST`. To retrieve values from input which are all `object`, you can use the dot operator (.) while to retrieve values from input which is a combination of `object` and `array`, you can use the square bracket operator `[]`.

Payload Example

```json
{
	"items":[
		{
			"name":"Kopi",
			"amount":15000
		},
		{
			"name":"Roti",
			"amount":80000
		}
	],
	"customer": {
		"name": "Anonim",
		"phone": "081111111111111"
	}
}

```

Example Configuration

```ini
PATH=/universal-rest-simulator/array

METHOD=POST

REQUEST_TYPE=application/json

RESPONSE_TYPE=text/plain

PARSING_RULE=\
$INPUT.AMOUNT0=$REQUEST[items][0][amount]\
$INPUT.AMOUNT1=$REQUEST[items][1][amount]\
$INPUT.NAME0=$REQUEST[items][0][name]\
$INPUT.NAME1=$REQUEST[items][1][name]

TRANSACTION_RULE=\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY=\
NAMA ITEM    : $INPUT.NAME0\
HARGA ITEM   : $INPUT.AMOUNT0\
NAMA ITEM    : $INPUT.NAME1\
HARGA ITEM   : $INPUT.AMOUNT1\
$INPUT.PARAMS\
{[ENDIF]}
```

The `[]` operator can be used to retrieve values from `object` and `array`. It is not allowed to combine the `.` and `[]` operators in retrieving a value. Thus, writing `$INPUT.AMOUNT0=$REQUEST[items][0].amount` is not allowed. However, it is permissible to use their combination on different inputs.

Example of Operator Combinations

```ini
PATH=/universal-rest-simulator/array

METHOD=POST

REQUEST_TYPE=application/json

RESPONSE_TYPE=text/plain

PARSING_RULE=\
$INPUT.CUSTOMER_NAME=$REQUEST.customer.name\
$INPUT.AMOUNT0=$REQUEST[items][0][amount]\
$INPUT.AMOUNT1=$REQUEST[items][1][amount]\
$INPUT.NAME0=$REQUEST[items][0][name]\
$INPUT.NAME1=$REQUEST[items][1][name]

TRANSACTION_RULE=\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY=\
NAMA PELANGGAN : $INPUT.CUSTOMER_NAME\
NAMA ITEM      : $INPUT.NAME0\
HARGA ITEM     : $INPUT.AMOUNT0\
NAMA ITEM      : $INPUT.NAME1\
HARGA ITEM     : $INPUT.AMOUNT1\
$INPUT.PARAMS\
{[ENDIF]}
```

`$INPUT.CUSTOMER_NAME=$REQUEST.customer.name` can also be written as `$INPUT.CUSTOMER_NAME=$REQUEST[customer][name]` without spaces before `[` and after `]`.

**Value of UUID**

Universal REST Simulator allows the use of UUIDs. To retrieve the UUID from the system, use `$SYSTEM.UUID`.

Example:

```ini
$INPUT.UUID=$SYSTEM.UUID\
$INPUT.RANDOM_ID=$SYSTEM.UUID\
$INPUT.UNIQ_ID=$SYSTEM.UUID
```
From the example above, `$INPUT.UUID`, `$INPUT.RANDOM_ID`, and `$INPUT.UNIQ_ID` will have different values.

**Examples of Encoded URL Input Configuration**

```ini
PATH=/biller/config1
METHOD=POST
REQUEST_TYPE=application/x-www-form-urlencoded
RESPONSE_TYPE=application/json
PARSING_RULE=\
$INPUT.PRODUCT=$REQUEST.product_code\
$INPUT.ACCOUNT=$REQUEST.customer_no\
$INPUT.REF_NUMBER=$REQUEST.refno
```

In the above configuration, `$INPUT.PRODUCT` will retrieve the value from `$REQUEST.product_code`. Thus, when a user makes a request with the URL `/biller/config1?product_code=10000&customer_no=081266612126&refno=5473248234`, then `$INPUT.PRODUCT` will be worth `10000`, and `$INPUT.ACCOUNT` will be `081266612126` etc.

**Examples of JSON Input Configuration**

```ini
PATH=/bank/config2
METHOD=POST
REQUEST_TYPE=application/json
RESPONSE_TYPE=application/json
PARSING_RULE=\
$INPUT.COMMAND=$REQUEST.command\
$INPUT.PRODUCT=$REQUEST.data.destination_bank_code\
$INPUT.ACCOUNT=$REQUEST.data.beneficiary_account_number\
$INPUT.REF_NUMBER=$REQUEST.data.customer_reference_number
```

JSON is usually used in `POST` or `PUT` methods. In the above configuration, `$INPUT.PRODUCT` will take the value from `$REQUEST.data.destination_bank_code` which is `ROOT.data.destination_bank_code`. where `ROOT` is a JSON object. Thus, when a user makes a request with

```
POST /bank/config2 HTTP/1.1 
Host: 127.0.0.1
Content-type: application/json
Content-length: 166

{
	"command":"inquiry",
	"data":{
		"destination_bank_code":"002",
		"beneficiary_account_number":"1234567890",
		"customer_reference_number":"9876544322"
	}
}
```

then `$INPUT.PRODUCT` will be worth `002`, likewise `$INPUT.ACCOUNT` will be `1234567890` and so on.

**Contoh Konfigurasi Input XML**

```ini
PATH=/bank/config3
METHOD=POST
REQUEST_TYPE=application/xml
RESPONSE_TYPE=application/xml
PARSING_RULE=\
$INPUT.COMMAND=$REQUEST.command\
$INPUT.PRODUCT=$REQUEST.product_code\
$INPUT.ACCOUNT=$REQUEST.customer_no\
$INPUT.AMOUNT=$REQUEST.amount\
$INPUT.REF_NUMBER=$REQUEST.refno
```

XML is usually used in `POST` or `PUT` methods. In the above configuration, `$INPUT.PRODUCT` will take the value from `$REQUEST.product_code` which is `ROOT.product_code`. where `ROOT` is the first level tag of the XML. Thus, when a user makes a request with

```
POST /bank/config3 HTTP/1.1 
Host: 127.0.0.1
Content-type: application/xml
Content-length: 196

<?xml  version="1.0"  encoding="UTF-8"?>
<data>
    <command>inquiry</command>
    <product_code>10001</product_code>
    <customer_no>081266612127</customer_no>
    <amount>5000000</amount>
    <refno>123456443</refno>
</data>
```

then `$INPUT.PRODUCT` will be worth `10001`, likewise `$INPUT.ACCOUNT` will be `081266612127` and so on.

## Combination of GET+POST and GET+PUT

Universal REST Simulator can combine `GET` input with `POST` or `PUT`. To combine `GET` with `POST`, use the `POST` method. To combine `GET` with `PUT`, use the `PUT` method.

`POST` and `PUT` only apply to `REQUEST_TYPE=application/x-www-form-urlencoded` . In this case, the client must also send `Content-type: application/x-www-form-urlencoded`. Taking input from `GET`, `POST`, and `PUT` is the same as `REQUEST` as in the following example:

```ini
PATH=/universal-simulator/token

METHOD=POST

REQUEST_TYPE=application/x-www-form-urlencoded

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.USERNAME=$AUTHORIZATION_BASIC.USERNAME\
$INPUT.PASSWORD=$AUTHORIZATION_BASIC.PASSWORD\
$INPUT.GRANT_TYPE=$POST.grant_type\
$INPUT.DETAIL=$GET.detail\
$INPUT.UUID1=$SYSTEM.UUID\
$INPUT.UUID2=$SYSTEM.UUID\
$INPUT.UUID3=$SYSTEM.UUID\
$INPUT.UUID4=$SYSTEM.UUID

TRANSACTION_RULE=\
{[IF]} ($INPUT.GRANT_TYPE == 'client_credentials' && $INPUT.USERNAME == "username" && $INPUT.PASSWORD == "password" && $INPUT.DETAIL == "yes")\
{[THEN]} $OUTPUT.DELAY=0\
$OUTPUT.DELAY=0\
$OUTPUT.BODY={\
    "token_type": "Bearer",\
    "access_token": "$TOKEN.JWT",\
    "expire_at": $TOKEN.EXPIRE_AT,\
    "expires_in": $TOKEN.EXPIRE_IN,\
    "econfig1l": "token@doconfig1n.tld"\
}\
{[ENDIF]}\
{[IF]} ($INPUT.GRANT_TYPE == 'client_credentials' && $INPUT.USERNAME == "username" && $INPUT.PASSWORD == "password")\
{[THEN]} $OUTPUT.DELAY=0\
$OUTPUT.DELAY=0\
$OUTPUT.BODY={\
    "token_type": "Bearer",\
    "access_token": "$TOKEN.JWT",\
    "expire_at": $TOKEN.EXPIRE_AT,\
    "expires_in": $TOKEN.EXPIRE_IN\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY={\
    "token_type": "Bearer",\
    "access_token": "$TOKEN.JWT",\
    "expire_at": $TOKEN.EXPIRE_AT,\
    "expires_in": $TOKEN.EXPIRE_IN\
}\
{[ENDIF]}\
```
The configuration above shows that the path requires the `POST` method and others. However, users can still retrieve values from queries at `URL` using `$GET`.

> Sample Request

```
POST /universal-simulator/token?detail=yes HTTP/1.1 
Host: 127.0.0.1
Authorization: Basic dXNlcm5hbWU6cGFzc3dvcmQ=
Content-type: application/x-www-form-urlencoded
Content-length: 29

grant_type=client_credentials
```

From the example above, the input from the URL `/universal-simulator/token?detail=yes` is taken with `$GET.detail`. This value will be equal to `$REQUEST.detail` if using the `GET` method. Because the configuration has defined `METHOD=POST`, this value can only be retrieved with `$GET.detail` because `$REQUEST` only refers to the request body sent.

Retrieval of data from the body can be done in two ways, namely `$REQUEST` and `$POST`. Note that `$POST` can only be used if `REQUEST_TYPE=application/x-www-form-urlencoded` and `Content-type: application/x-www-form-urlencoded`. For other content types, must use `$RQUEST`.

## $CALC() Function

The `$CALC()` function is very useful for performing mathematical operations where `$INPUT` is one of the operands.

For example: the user will add the bill amount with admin fee. If the invoice is stored in the variable `$INPUT.AMOUNT` and the admin fee is stored in the variable `$INPUT.FEE`, it can be written as `$CALC($INPUT.AMOUNT + $INPUT.FEE)`. If the admin fee is a fixed value of 2500, it can be written as `$CALC($INPUT.AMOUNT + 2500)`.

The `$CALC()` function can also calculate formulas in brackets. Example: `$CALC($INPUT.AMOUNT + $INPUT.FEE + ($INPUT.AMOUNT * 10/100))` and so on. Note that the number of opening brackets must equal the number of closing brackets.

```ini
PATH=/biller/post/json

METHOD=POST

REQUEST_TYPE=application/json

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.PRODUCT=$REQUEST.product_code\
$INPUT.ACCOUNT=$REQUEST.customer_no\
$INPUT.REF_NUMBER=$REQUEST.refno\
$INPUT.AMOUNT=$REQUEST.amount\
$INPUT.FEE=$REQUEST.admin_fee

TRANSACTION_RULE=\
{[IF]} ($INPUT.PRODUCT == "322112" && $INPUT.FEE > 0)\
{[THEN]} $OUTPUT.DELAY=0\
$OUTPUT.DELAY=0\
$OUTPUT.BODY={\
   "rc": "00",\
   "description": "Success",\
   "mitra_code": "904",\
   "product_code": "322112",\
   "merchant_type": "5612",\
   "customer_no": "$INPUT.ACCOUNT",\
   "product_name": "GOPAY",\
   "phone_number": "$INPUT.ACCOUNT",\
   "name": "GOPAY GP-$INPUT.ACCOUNT",\
   "amount": $INPUT.AMOUNT,\
   "admin": $INPUT.FEE,\
   "total": $CALC($INPUT.AMOUNT + $INPUT.FEE),\
   "transaction_date": "$DATE('d-m-Y H:i:s', 'UTC+9')",\
   "transaction_code": "000002873147"\
}\
{[ENDIF]}\
{[IF]} ($INPUT.PRODUCT == "322112")\
{[THEN]} $OUTPUT.DELAY=0\
$OUTPUT.DELAY=0\
$OUTPUT.BODY={\
   "rc": "00",\
   "description": "Success",\
   "mitra_code": "904",\
   "product_code": "322112",\
   "merchant_type": "5612",\
   "customer_no": "$INPUT.ACCOUNT",\
   "product_name": "GOPAY",\
   "phone_number": "$INPUT.ACCOUNT",\
   "name": "GOPAY GP-$INPUT.ACCOUNT",\
   "amount": $INPUT.AMOUNT,\
   "admin": 2500,\
   "total": $CALC($INPUT.AMOUNT + 2500),\
   "transaction_date": "$DATE('d-m-Y H:i:s', 'UTC+9')",\
   "transaction_code": "000002873147"\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY={\
   "rc": "00",\
   "description": "Success",\
   "mitra_code": "904",\
   "product_code": "322112",\
   "merchant_type": "5612",\
   "customer_no": "$INPUT.ACCOUNT",\
   "product_name": "GOPAY",\
   "phone_number": "$INPUT.ACCOUNT",\
   "name": "GOPAY GP-$INPUT.ACCOUNT",\
   "amount": $INPUT.AMOUNT,\
   "admin": 2500,\
   "total": $CALC('$INPUT.AMOUNT + 2500'),\
   "transaction_date": "$DATE('d-m-Y H:i:s')",\
   "transaction_code": "000002873147"\
}\
{[ENDIF]}\
```

## $DATE() Function

The `$DATE()` function is useful for generating the date and time automatically. The date and time will follow the server time. User can use time zone.

The `$DATE()` format follows the format in the PHP programming language. The following is an explanation of the `$DATE()` format in the PHP programming language. To insert a constant character in the `$DATE()` function, prefix it with `\`. For example `$DATE('Y-m-d\TH:i:s.000\Z', 'UTC+7')` will return `2020-10:10T20:20:20,000Z`. Note that `\T` will become `T` and `\Z` will become `Z`.

| Format character  | Description | Example returned values |
|-------------------|------------ |------------------------ |
| Day | --- | --- |
| d | Day of the month, 2 digits with leading zeros | 01 to 31 |
| D | A textual representation of a day, three letters | Mon through Sun |
| j | Day of the month without leading zeros | 1 to 31 |
| l (lowercase 'L') | A full textual representation of the day of the week | Sunday through Saturday |
| N | ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0) | 1 (for Monday) through 7 (for Sunday) |
| S | English ordinal suffix for the day of the month, 2 characters | st, nd, rd or th. Works well with j |
| w | Numeric representation of the day of the week | 0 (for Sunday) through 6 (for Saturday) |
| z | The day of the year (starting from 0) | 0 through 365 |
| Week | --- | --- |
| W | ISO-8601 week number of year, weeks starting on Monday | Example: 42 (the 42nd week in the year) |
| Month | --- | --- |
| F | A full textual representation of a month, such as January or March | January through December |
| m | Numeric representation of a month, with leading zeros | 01 through 12 |
| M | A short textual representation of a month, three letters | Jan through Dec |
| n | Numeric representation of a month, without leading zeros | 1 through 12 |
| t | Number of days in the given month | 28 through 31 |
| Year | --- | --- |
| L | Whether it's a leap year | 1 if it is a leap year, 0 otherwise. |
| o | ISO-8601 week-numbering year. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead. (added in PHP 5.1.0) | Examples: 1999 or 2003 |
| Y | A full numeric representation of a year, 4 digits | Examples: 1999 or 2003 |
| y | A two digit representation of a year | Examples: 99 or 03 |
| Time | --- | --- |
| a | Lowercase Ante meridiem and Post meridiem | am or pm |
| A | Uppercase Ante meridiem and Post meridiem | AM or PM |
| B | Swatch Internet time | 000 through 999 |
| g | 12-hour format of an hour without leading zeros | 1 through 12 |
| G | 24-hour format of an hour without leading zeros | 0 through 23 |
| h | 12-hour format of an hour with leading zeros | 01 through 12 |
| H | 24-hour format of an hour with leading zeros | 00 through 23 |
| i | Minutes with leading zeros | 00 to 59 |
| s | Seconds with leading zeros | 00 through 59 |
| u | Microseconds (added in PHP 5.2.2). Note that date() will always generate 000000 since it takes an int parameter, whereas DateTime::format() does support microseconds if DateTime was created with microseconds. | Example: 654321 |
| v | Milliseconds (added in PHP 7.0.0). Same note applies as for u. | Example: 654 |
| Timezone | --- | --- |
| e | Timezone identifier (added in PHP 5.1.0) | Examples: UTC, GMT, Atlantic/Azores |
| I (capital i) | Whether or not the date is in daylight saving time | 1 if Daylight Saving Time, 0 otherwise. |
| O | Difference to Greenwich time (GMT) without colon between hours and minutes | Example: +0200 |
| P | Difference to Greenwich time (GMT) with colon between hours and minutes (added in PHP 5.1.3) | Example: +02:00 |
| T | Timezone abbreviation | Examples: EST, MDT ... |
| Z | Timezone offset in seconds. The offset for timezones west of UTC is always negative, and for those east of UTC is always positive. | -43200 through 50400 |
| Full Date/Time | --- | --- |
| c | ISO 8601 date (added in PHP 5) | 2004-02-12T15:19:21+00:00 |
| r | » RFC 2822 formatted date | Example: Thu, 21 Dec 2000 16:01:07 +0200 |
| U | Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT) | See also time() |

**Sumber**: https://www.php.net/manual/en/datetime.format.php 

## $ISVALIDTOKEN() Function

This function is used in conditions to validate tokens sent via `Authorization: Bearer`. The simulator will fetch the token sent via the header with the key `Authorization`. This token will then be validated according to the server configuration. If the token is true, `$ISVALIDTOKEN()` will be `true`. Otherwise, if the token is false, `$ISVALIDTOKEN()` will return `false`. The simulator will only validate tokens generated by the simulator itself.

**How to Create Token**

In order for the simulator to generate tokens, create a configuration file. The output of the configuration must contain `$TOKEN.JWT`. Other information such as `$TOKEN.EXPIRE_AT` and `$TOKEN.EXPIRE_IN` can also be added.

**Configure Request Token**

```ini
PATH=/auth

METHOD=POST

REQUEST_TYPE=application/x-www-form-urlencoded

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.USERNAME=$AUTHORIZATION_BASIC.USERNAME\
$INPUT.PASSWORD=$AUTHORIZATION_BASIC.PASSWORD\
$INPUT.GRANT_TYPE=$REQUEST.grant_type

TRANSACTION_RULE=\
{[IF]} ($INPUT.USERNAME == 'username' && $INPUT.PASSWORD == 'userpassword' && $INPUT.GRANT_TYPE == 'client_credentials')\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY={\
    "token_type": "Bearer",\
    "access_token": "$TOKEN.JWT",\
    "expire_at": $TOKEN.EXPIRE_AT,\
    "expires_in": $TOKEN.EXPIRE_IN\
}\
{[ENDIF]}\
```

**How to Validate Token**

To do token validation, it's very easy. It is enough to create the condition `$ISVALIDTOKEN()`. The simulator will take the token sent and validate the token.

**Token Validation Configuration**

```ini
PATH=/Universal-REST-Simulator/va-status

METHOD=POST

REQUEST_TYPE=application/json

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.COMMAND=$REQUEST.command\
$INPUT.VA_NUMBER=$REQUEST.data.virtual_account_number\
$INPUT.REQUEST_ID=$REQUEST.data.request_id\
$INPUT.PG_CODE=$REQUEST.data.pg_code\
$INPUT.REF_NUMBER=$REQUEST.data.reference_number\
$INPUT.CUST_NUMBER=$REQUEST.data.customer_number\
$INPUT.CUST_NAME=$REQUEST.data.customer_name\
$INPUT.BANK_CODE=$REQUEST.data.bank_code\
$INPUT.CHANNEL_TYPE=$REQUEST.data.channel_type\
$INPUT.TOTAL_AMOUNT=$REQUEST.data.total_amount\
$INPUT.PAID_AMOUNT=$REQUEST.data.paid_amount

TRANSACTION_RULE=\
{[IF]} ($INPUT.CUST_NUMBER == "1571200004" && $ISVALIDTOKEN())\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY={\
    "command": "$INPUT.COMMAND",\
    "response_code": "00",\
    "response_text": "Success",\
    "message": {\
        "id": "Sukses",\
        "en": "Success"\
    },\
    "data": {\
        "bank_code": "$INPUT.BANK_CODE",\
        "channel_type": "$INPUT.CHANNEL_TYPE",\
        "pg_code": "$INPUT.PG_CODE",\
        "merchant_code": "030",\
        "merchant_name": "Arta Pay",\
        "virtual_account_number": "$INPUT.VA_NUMBER",\
        "customer_name": "$INPUT.CUST_NAME",\
        "request_id": "$INPUT.REQUEST_ID",\
        "reference_number": "$INPUT.REF_NUMBER",\
        "time_stamp": "$DATE('Y-m-d\TH:i:s', 'UTC').000Z",\
        "customer_number": "$INPUT.CUST_NUMBER",\
        "currency_code": "IDR",\
        "total_amount": $INPUT.TOTAL_AMOUNT,\
        "paid_amount": $INPUT.PAID_AMOUNT,\
        "bill_list": []\
    }\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY={\
    "command": "$INPUT.COMMAND",\
    "response_code": "05",\
    "response_text": "Invalid Token",\
    "message": {\
        "id": "Sukses",\
        "en": "Success"\
    },\
    "data": {\
        "time_stamp": "$DATE('Y-m-d\TH:i:s', 'UTC').000Z"\
    }\
}\
{[ENDIF]}\
```

The configuration above shows an example of how to validate a token. Note that `$INPUT.USERNAME=$AUTHORIZATION_BASIC.USERNAME` and `$INPUT.PASSWORD=$AUTHORIZATION_BASIC.PASSWORD` can no longer be used because the `Authorization` key has already been used to send tokens. However, you can still use other keys.

**Configure Token Validation with Additional Header**

```ini
PATH=/Universal-REST-Simulator/va-status

METHOD=POST

REQUEST_TYPE=application/json

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.USERNAME=$HEADER.X_USERNAME\
$INPUT.PASSWORD=$HEADER.X_PASSWORD\
$INPUT.API_KEY=$HEADER.X_API_KEY\
$INPUT.COMMAND=$REQUEST.command\
$INPUT.VA_NUMBER=$REQUEST.data.virtual_account_number\
$INPUT.REQUEST_ID=$REQUEST.data.request_id\
$INPUT.PG_CODE=$REQUEST.data.pg_code\
$INPUT.REF_NUMBER=$REQUEST.data.reference_number\
$INPUT.CUST_NUMBER=$REQUEST.data.customer_number\
$INPUT.CUST_NAME=$REQUEST.data.customer_name\
$INPUT.BANK_CODE=$REQUEST.data.bank_code\
$INPUT.CHANNEL_TYPE=$REQUEST.data.channel_type\
$INPUT.TOTAL_AMOUNT=$REQUEST.data.total_amount\
$INPUT.PAID_AMOUNT=$REQUEST.data.paid_amount

TRANSACTION_RULE=\
{[IF]} ($INPUT.USERNAME == 'username' && $INPUT.PASSWORD == 'userpassword' && $INPUT.API_KEY == 'API123' && $INPUT.CUST_NUMBER == "1571200004" && $ISVALIDTOKEN())\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY={\
    "command": "$INPUT.COMMAND",\
    "response_code": "00",\
    "response_text": "Success",\
    "message": {\
        "id": "Sukses",\
        "en": "Success"\
    },\
    "data": {\
        "bank_code": "$INPUT.BANK_CODE",\
        "channel_type": "$INPUT.CHANNEL_TYPE",\
        "pg_code": "$INPUT.PG_CODE",\
        "merchant_code": "030",\
        "merchant_name": "Arta Pay",\
        "virtual_account_number": "$INPUT.VA_NUMBER",\
        "customer_name": "$INPUT.CUST_NAME",\
        "request_id": "$INPUT.REQUEST_ID",\
        "reference_number": "$INPUT.REF_NUMBER",\
        "time_stamp": "$DATE('Y-m-d\TH:i:s', 'UTC').000Z",\
        "customer_number": "$INPUT.CUST_NUMBER",\
        "currency_code": "IDR",\
        "total_amount": $INPUT.TOTAL_AMOUNT,\
        "paid_amount": $INPUT.PAID_AMOUNT,\
        "bill_list": []\
    }\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY={\
    "command": "$INPUT.COMMAND",\
    "response_code": "05",\
    "response_text": "Invalid Token",\
    "message": {\
        "id": "Sukses",\
        "en": "Success"\
    },\
    "data": {\
        "time_stamp": "$DATE('Y-m-d\TH:i:s', 'UTC').000Z"\
    }\
}\
{[ENDIF]}\
```

## Selection of Conditions

**Propety: `TRANSACTION_RULE`**

The simulator only supports `IF` and not `ELSE` conditions. All generated data is data that is between `{[THEN]}` and `{[ENDIF]}`. `IF` is written as `{[IF]}`, `THEN` is written as `{[THEN]}`, and `ENDIF` is written as `{[ENDIF]}`. This is to distinguish between reserved words and words that may appear in the configuration data.

The simulator will evaluate the expression on `{[IF]}`. If the condition evaluates to `true`, then the simulator will retrieve all data in that block regardless of whether the condition in the next block is `true` or `false`.

Some of the data that can be generated by the simulator are as follows:


1. `$OUTPUT.STATUS` is the HTTP status code. The default value of `$OUTPUT.STATUS` is `200`
2. `$OUTPUT.HEADER` is a line response header.
3. `$OUTPUT.DELAY` is the timeout. The server will wait a while before sending a response.
4. `$OUTPUT.BODY` is the response body.
5. `$OUTPUT.CALLBACK_URL` is the URL that the callback process goes to.
6. `$OUTPUT.CALLBACK_METHOD` is the method of the callback. The methods that can be used are `GET`, `POST`, and `PUT`.
7. `$OUTPUT.CALLBACK_TYPE` is the content type for the callback. This content type is free as needed.
8. `$OUTPUT.CALLBACK_TIMEOUT` is the timeout for the callback.
9. `$OUTPUT.CALLBACK_HEADER` is the request header for the callback.
10. `$OUTPUT.CALLBACK_BODY` is the request body for the callback.
11. `$OUTPUT.CALLBACK_DELAY` is the delay for the callback in milliseconds.

An explanation of callbacks can be read in the **Callback** section.

```ini
PATH=/bank/config2
METHOD=POST
REQUEST_TYPE=application/json
RESPONSE_TYPE=application/json
PARSING_RULE=\
$INPUT.COMMAND=$REQUEST.command\
$INPUT.PRODUCT=$REQUEST.data.destination_bank_code\
$INPUT.ACCOUNT=$REQUEST.data.beneficiary_account_number\
$INPUT.REF_NUMBER=$REQUEST.data.customer_reference_number
TRANSACTION_RULE=\
{[IF]} ($INPUT.COMMAND == "inquiry" && $INPUT.PRODUCT == "002" && $INPUT.ACCOUNT == "1234567890")\
{[THEN]}\
$OUTPUT.STATUS=200\
$OUTPUT.DELAY=0\
$OUTPUT.BODY=\
{\
	"rc":"00",\
	"sn":"82634862385235365285",\
	"nama":"config2",\
	"customer_no":"$INPUT.ACCOUNT",\
	"product_code":"$INPUT.PRODUCT",\
	"time_stamp":"$DATE('j F Y H:i:s', 'UTC+7')",\
	"msg":"Transaksi ini dikenakan biaya Rp. 250",\
	"refid":"$INPUT.REF_NUMBER"\
}\
{[ENDIF]}\
{[IF]} ($INPUT.COMMAND == "inquiry" && $INPUT.PRODUCT == "002" && $INPUT.ACCOUNT == "1234567891")\
{[THEN]}\
$OUTPUT.DELAY=20000\
$OUTPUT.BODY=\
{\
	"rc":"00",\
	"sn":"82634862385235365285",\
	"nama":"Budi",\
	"customer_no": "$INPUT.ACCOUNT",\
	"product_code": "$INPUT.PRODUCT",\
	"time_stamp": "$DATE('Y-m-d H:i:s')",\
	"msg": "Transaksi ini dikenakan biaya Rp. 250",\
	"refid": "873264832658723585"\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY=\
{\
	"rc": "25",\
	"sn": "82634862385235365285",\
	"nama": "Budi",\
	"customer_no": "$INPUT.ACCOUNT",\
	"product_code": "$INPUT.PRODUCT",\
	"time_stamp": "$DATE('Y-m-d H:i:s')",\
	"msg": "Transaksi ini dikenakan biaya Rp. 250",\
	"refid": "873264832658723585"\
}\
{[ENDIF]}\
```

The simulator will evaluate the conditions accordingly. If there are two conditions

## HTTP Status

The Universal REST Simulator allows the use of non-standard HTTP Status. HTTP Status Standard can be found at https://developer.mozilla.org/id/docs/Web/HTTP/Status

For standard HTTP Status, users just need to write the code. For example `$OUTPUT.STATUS=200` or `$OUTPUT.STATUS=403`. For use of non-standard HTTP Status, users need to add a description text behind the code. For example: `$OUTPUT.STATUS=699 Under Maintenance`. If the user does not add a description behind the code, then Universal REST Simulator will send HTTP Status 500 or Internal Server Error.

## Example of URL-Encoded Request Configuration

> Sample Request

```
GET /biller/config1?product_code=10000&customer_no=081266612126&refno=5473248234 HTTP/1.1 
Host: 127.0.0.1
Content-type: application/json
Content-length: 166
```

**Konfigurasi**

```ini
PATH=/biller/config1
METHOD=POST
REQUEST_TYPE=application/x-www-form-urlencoded
RESPONSE_TYPE=application/json
PARSING_RULE=\
$INPUT.PRODUCT=$REQUEST.product_code\
$INPUT.ACCOUNT=$REQUEST.customer_no\
$INPUT.REF_NUMBER=$REQUEST.refno
TRANSACTION_RULE=\
{[IF]} ($INPUT.PRODUCT == "10000" && $INPUT.ACCOUNT == "081266612126")\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY=\
{\
	"rc": "00",\
	"sn": "82634862385235365285",\
	"nama": "config2",\
	"customer_no": "$INPUT.ACCOUNT",\
	"product_code": "$INPUT.PRODUCT",\
	"time_stamp": "$DATE('j F Y H:i:s', 'UTC+7')",\
	"msg": "Transaksi ini dikenakan biaya Rp. 250",\
	"refid": "$INPUT.REF_NUMBER"\
}\
{[ENDIF]}\
{[IF]} ($INPUT.PRODUCT == "10000" && $INPUT.ACCOUNT == "081266612127")\
{[THEN]}\
$OUTPUT.DELAY=20000\
$OUTPUT.BODY=\
{\
	"rc": "00",\
	"sn": "82634862385235365285",\
	"nama": "Budi",\
	"customer_no": "$INPUT.ACCOUNT",\
	"product_code": "$INPUT.PRODUCT",\
	"time_stamp": "$DATE('Y-m-d H:i:s')",\
	"msg": "Transaksi ini dikenakan biaya Rp. 250",\
	"refid": "873264832658723585"\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]} $OUTPUT.DELAY=0\
$OUTPUT.BODY=\
{\
	"rc": "25",\
	"sn": "82634862385235365285",\
	"nama": "Budi",\
	"customer_no": "$INPUT.ACCOUNT",\
	"product_code": "$INPUT.PRODUCT",\
	"time_stamp": "$DATE('Y-m-d H:i:s')",\
	"msg": "Transaksi ini dikenakan biaya Rp. 250",\
	"refid": "873264832658723585"\
}\
{[ENDIF]}\
```

## Example of JSON Request Configuration

On a JSON request, it is possible that the `key` of JSON contains characters other than `alpha numeric` and `underscore`. To allow the simulator to read input from the request, replace all characters other than `alpha numeric` and `underscore` with `underscore` or `_`.

> Contoh Request

```
POST /bank/config2 HTTP/1.1 
Host: 127.0.0.1
Content-type: application/json
Content-length: 166

{
	"command":"inquiry",
	"data":{
		"destination_bank_code": "002",
		"beneficiary_account_number": "1234567890",
		"customer_reference_number": "9876544322"
	}
}
```

**Konfigurasi**

```ini
PATH=/bank/config2
METHOD=POST
REQUEST_TYPE=application/json
RESPONSE_TYPE=application/json
PARSING_RULE=\
$INPUT.COMMAND=$REQUEST.command\
$INPUT.PRODUCT=$REQUEST.data.destination_bank_code\
$INPUT.ACCOUNT=$REQUEST.data.beneficiary_account_number\
$INPUT.REF_NUMBER=$REQUEST.data.customer_reference_number
TRANSACTION_RULE=\
{[IF]} ($INPUT.COMMAND == "inquiry" && $INPUT.PRODUCT == "002" && $INPUT.ACCOUNT == "1234567890")\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY=\
{\
	"rc": "00",\
	"sn": "82634862385235365285",\
	"nama": "config2",\
	"customer_no": "$INPUT.ACCOUNT",\
	"product_code": "$INPUT.PRODUCT",\
	"time_stamp": "$DATE('j F Y H:i:s', 'UTC+7')",\
	"msg": "Transaksi ini dikenakan biaya Rp. 250",\
	"refid": "$INPUT.REF_NUMBER"\
}\
{[ENDIF]}\
{[IF]} ($INPUT.COMMAND == "inquiry" && $INPUT.PRODUCT == "002" && $INPUT.ACCOUNT == "1234567891")\
{[THEN]} $OUTPUT.DELAY=20000\
$OUTPUT.BODY=\
{\
	"rc": "00",\
	"sn": "82634862385235365285",\
	"nama": "Budi",\
	"customer_no": "$INPUT.ACCOUNT",\
	"product_code": "$INPUT.PRODUCT",\
	"time_stamp": "$DATE('Y-m-d H:i:s')",\
	"msg": "Transaksi ini dikenakan biaya Rp. 250",\
	"refid": "873264832658723585"\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY=\
{\
	"rc": "25",\
	"sn": "82634862385235365285",\
	"nama": "Budi",\
	"customer_no": "$INPUT.ACCOUNT",\
	"product_code": "$INPUT.PRODUCT",\
	"time_stamp": "$DATE('Y-m-d H:i:s')",\
	"msg": "Transaksi ini dikenakan biaya Rp. 250",\
	"refid": "873264832658723585"\
}\
{[ENDIF]}\
```

## Example of XML Request Configuration

> Sample Request

```
POST /bank/config3 HTTP/1.1 
Host: 127.0.0.1
Content-type: application/xml
Content-length: 196

<?xml  version="1.0"  encoding="UTF-8"?>
<data>
    <command>inquiry</command>
    <product_code>10001</product_code>
    <customer_no>081266612127</customer_no>
    <amount>5000000</amount>
    <refno>123456443</refno>
</data>
```

**Configuration**

```ini
PATH=/bank/config3

METHOD=POST

REQUEST_TYPE=application/xml

RESPONSE_TYPE=application/xml

PARSING_RULE=\
$INPUT.PRODUCT=$REQUEST.product_code\
$INPUT.ACCOUNT=$REQUEST.customer_no\
$INPUT.REF_NUMBER=$REQUEST.refno\
$INPUT.AMOUNT=$REQUEST.amount

TRANSACTION_RULE=\
{[IF]} ($INPUT.PRODUCT == "10000" && $INPUT.ACCOUNT == "081266612126" && $INPUT.AMOUNT > 0)\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY=\<?xml version="1.0" encoding="UTF-8"?>\
<data>\
\
	<rc>00</rc>\
	<sn>82634862385235365285</sn>\
	<nama>config2</nama>\
	<customer_no>$INPUT.ACCOUNT</customer_no>\
	<product_code>$INPUT.PRODUCT</product_code>\
	<time_stamp>$DATE('j F Y H:i:s', 'UTC+7')</time_stamp>\
	<msg>Transaksi ini dikenakan biaya Rp. 250</msg>\
	<refid>$INPUT.REF_NUMBER</refid>\
<data>\
{[ENDIF]}\
{[IF]} ($INPUT.PRODUCT == "10001" && $INPUT.ACCOUNT == "081266612127")\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY=\<?xml version="1.0" encoding="UTF-8"?>\
<data>\
\
	<rc>00</rc>\
	<sn>82634862385235365285</sn>\
	<nama>config2</nama>\
	<customer_no>$INPUT.ACCOUNT</customer_no>\
	<product_code>$INPUT.PRODUCT</product_code>\
	<time_stamp>$DATE('j F Y H:i:s', 'UTC+7')</time_stamp>\
	<msg>Transaksi ini dikenakan biaya Rp. 250</msg>\
	<refid>$INPUT.REF_NUMBER</refid>\
<data>\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]} $OUTPUT.DELAY=0\
$OUTPUT.BODY=\<?xml version="1.0" encoding="UTF-8"?>\
<data>\
\
	<rc>25</rc>\
	<sn>82634862385235365285</sn>\
	<nama>config2</nama>\
	<customer_no>$INPUT.ACCOUNT</customer_no>\
	<product_code>$INPUT.PRODUCT</product_code>\
	<time_stamp>$DATE('j F Y H:i:s', 'UTC+7')</time_stamp>\
	<msg>Pelanggan tidak ditemukan</msg>\
	<refid>$INPUT.REF_NUMBER</refid>\
<data>\
{[ENDIF]}\
```

## Callback

Some simulators send callbacks due to asynchronous processes. Universal REST Simulator also supports callbacks. The callback will be sent after the request has been parsed before delaying response (if any).

To add a callback, some configuration needs to be made on `TRANSACTION_RULE` which is as follows:

1. `$OUTPUT.CALLBACK_URL` is the URL that the callback process goes to.
2. `$OUTPUT.CALLBACK_METHOD` is the method of the callback. The methods that can be used are `GET`, `POST`, and `PUT`.
3. `$OUTPUT.CALLBACK_TYPE` is the content type for the callback. This content type is free as needed.
4. `$OUTPUT.CALLBACK_HEADER` is the request header for the callback.
5. `$OUTPUT.CALLBACK_BODY` is the request body for the callback.
6. `$OUTPUT.CALLBACK_DELAY` is the delay for the callback in milliseconds.

The default method is `GET`. If `$OUTPUT.CALLBACK_METHOD` is `GET`, then `$OUTPUT.CALLBACK_BODY` will not be sent and the `Content-length` and `Content-type` in headers will also not be sent.

Note that in the `POST` and `PUT` methods, the user must specify `$OUTPUT.CALLBACK_BODY` because the destination server will wait for the simulator to send `body` to the callback process. `$OUTPUT.CALLBACK_TYPE` on `POST` and `PUT` must also be set to specify `Content-type` on callbacks. The `content-length` will be generated automatically by the simulator when the callback is made. User can add `User-agent` in header. If the user does not enter `User-agent`, the simulator will create a default `User-agent` because some servers may require each request to include a `User-agent`.

**Configuration**

```ini
PATH=/universal-rest-simulator/xml

METHOD=POST

REQUEST_TYPE=application/xml

RESPONSE_TYPE=application/xml

PARSING_RULE=\
$INPUT.PRODUCT=$REQUEST.product_code\
$INPUT.ACCOUNT=$REQUEST.customer_no\
$INPUT.REF_NUMBER=$REQUEST.refno\
$INPUT.AMOUNT=$REQUEST.amount

TRANSACTION_RULE=\
{[IF]} ($INPUT.PRODUCT == "10000" && $INPUT.ACCOUNT == "081266612126" && $INPUT.AMOUNT > 0)\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.CALLBACK_URL=http://localhost/test/\
$OUTPUT.CALLBACK_METHOD=POST\
$OUTPUT.CALLBACK_TYPE=application/xml\
$OUTPUT.CALLBACK_HEADER=X-Server-Name: Universal REST Simulator\
$OUTPUT.CALLBACK_DELAY=1500\
X-Response-Code: 00\
X-Response-Text: Success\
$OUTPUT.CALLBACK_DELAY=200\
$OUTPUT.CALLBACK_BODY=<?xml version="1.0" encoding="UTF-8"?>\
<data>\
\
	<rc>00</rc>\
	<sn>82634862385235365285</sn>\
	<nama>config2</nama>\
	<customer_no>$INPUT.ACCOUNT</customer_no>\
	<product_code>$INPUT.PRODUCT</product_code>\
	<time_stamp>$DATE('j F Y H:i:s', 'UTC+7')</time_stamp>\
	<msg>Ini output dari callback Transaksi ini dikenakan biaya Rp. 250</msg>\
	<refid>$INPUT.REF_NUMBER</refid>\
<data>\
$OUTPUT.HEADER=X-Server-Name: Universal REST Simulator\
X-Response-Code: 00\
X-Response-Text: Success\
$OUTPUT.BODY=<?xml version="1.0" encoding="UTF-8"?>\
<data>\
\
	<rc>00</rc>\
	<sn>82634862385235365285</sn>\
	<nama>config2</nama>\
	<customer_no>$INPUT.ACCOUNT</customer_no>\
	<product_code>$INPUT.PRODUCT</product_code>\
	<time_stamp>$DATE('j F Y H:i:s', 'UTC+7')</time_stamp>\
	<msg>Transaksi ini dikenakan biaya Rp. 250</msg>\
	<refid>$INPUT.REF_NUMBER</refid>\
<data>\
{[ENDIF]}\
{[IF]} ($INPUT.PRODUCT == "10001" && $INPUT.ACCOUNT == "081266612127")\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY=<?xml version="1.0" encoding="UTF-8"?>\
<data>\
\
	<rc>00</rc>\
	<sn>82634862385235365285</sn>\
	<nama>config2</nama>\
	<customer_no>$INPUT.ACCOUNT</customer_no>\
	<product_code>$INPUT.PRODUCT</product_code>\
	<time_stamp>$DATE('j F Y H:i:s', 'UTC+7')</time_stamp>\
	<msg>Transaksi ini dikenakan biaya Rp. 250</msg>\
	<refid>$INPUT.REF_NUMBER</refid>\
<data>\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.BODY=<?xml version="1.0" encoding="UTF-8"?>\
<data>\
\
	<rc>25</rc>\
	<sn>82634862385235365285</sn>\
	<nama>config2</nama>\
	<customer_no>$INPUT.ACCOUNT</customer_no>\
	<product_code>$INPUT.PRODUCT</product_code>\
	<time_stamp>$DATE('j F Y H:i:s', 'UTC+7')</time_stamp>\
	<msg>Pelanggan tidak ditemukan</msg>\
	<refid>$INPUT.REF_NUMBER</refid>\
<data>\
{[ENDIF]}\
```

## Native PHP Code

In some cases, the simulator cannot be created with a configuration file alone. Processes that are too complex to be handled by programming languages include saving transaction data to a database or file system.

Universal REST Simulator allows users to create their own native PHP code.

This method is actually not recommended because it can harm the simulator. But it can be done if the user understands the PHP language well.

To write native PHP code on the simulator is very easy. First define METHOD and PATH then write PHP code between `{[EVAL_PHP_BEGIN]}` and `{[EVAL_PHP_END]}`

Parsing only input data is done manually depending on the `method` and `content-type` sent by the client.

Example Configuration

```
METHOD=POST
PATH=/encrypted/payload/

{[EVAL_PHP_BEGIN]}
function encryptEBC($key, $payload){
    return bin2hex(openssl_encrypt($payload, 'aes-128-ecb', $key, OPENSSL_RAW_DATA));
}
function decriptEBC($key, $payload){
    return openssl_decrypt(hex2bin($payload), 'aes-128-ecb', $key, OPENSSL_RAW_DATA);
}

$rc = '00';
$key = "iY87^R76R%e4d7tD";

$clientID = @$_POST['login'];
$pwd = @$_POST['pwd'];
$terminal = @$_POST['terminal'];
$customer = @$_POST['customer'];
$trx_date = @$_POST['trx_date'];
$trx_type = @$_POST['trx_type'];
$sequence_id = @$_POST['sequence_id'];

$tx_amount = @$_POST['tx_amount'];
$isreversal = @$_POST['isreversal'];

$pwd = decriptEBC($key, $pwd);
$terminal = decriptEBC($key, $terminal);
$customer = decriptEBC($key, $customer);

$trx_date = decriptEBC($key, $trx_date);
$trx_type = decriptEBC($key, $trx_type);
$sequence_id = decriptEBC($key, $sequence_id);
if($customer == '0725553725')
{
    $rc = '00';
}
header("Content-type: text/plain");
echo "$rc:$sequence_id";
{[EVAL_PHP_END]}
```

## File Manager

Universal REST Simulator comes with a file manager for creating, modifying and deleting configuration files. The file manager is equipped with a username and password to enter into it. The file manager can be accessed with the path `/filemanager/` from the document root of the simulator.

The file manager username and password are stored in the `.htpasswd` file in the `filemanager` directory. Username and password can be added by adding a line at the end of the file. The supported password encryption methods are:

1. SHA with `{SHA}` . prefix
2. APR1 with prefix `$apr1$`

Usernames and passwords can be generated using the Htpasswd Generator which is widely available online.

A complete guide to using the file manager can be found at https://github.com/kamshory/PlanetbiruFileManager

## Check Path

Universal REST Simulator uses `path` and `method` to determine which configuration to use. If there are two or more pairs of the same `path` and `method`, the configuration file that is found first will be used.

Users need to know whether the `path` and `method` have been used or not or whether there is duplication or not. To check the `path` and `method` used, the user can simply access /checkpath/ from the document root of the simulator using a browser.
