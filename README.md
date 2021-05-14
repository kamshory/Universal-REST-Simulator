

# Universal REST Simulator

## Pengenalan

Universal REST Simulator adalah simulator universal untuk REST API. Simulator ini dapat dikonfigurasi dengan menambahkan beberapa file konfigurasi ke dalam direktori `config`. Universal REST Simulator dapat membaca semua ekstensi file.

Konfigurasi mirip dengan file `ini`. Apabila nilai konfigurasi lebih dari satu baris, maka nilai pada baris selain terakhir harus diakhiri dengan `\` (backslash).

Totorial dapat dibaca di https://github.com/kamshory/Universal-REST-Simulator/blob/main/tutorial.md 

Contoh:

```ini
PARSING_RULE=\
$INPUT.PRODUCT=$REQUEST.product_code\
$INPUT.ACCOUNT=$REQUEST.customer_no\
$INPUT.REF_NUMBER=$REQUEST.refno\
$INPUT.ACCEPT_LANGUAGE=$OUTPUT.HEADER.ACCEPT_LANGUAGE\
$INPUT.AMOUNT=$REQUEST.amount
```

Property `PARSING_RULE` di atas terdiri dari 4 baris. Apabila karakter `\` dihilangkan, maka `PARSING_RULE` tidak ada nilainya. Apabila `\` di baris 2 dihilangkan menjadi sebagai berikut:

```ini
PARSING_RULE=\
$INPUT.PRODUCT=$REQUEST.product_code\
$INPUT.ACCOUNT=$REQUEST.customer_no
$INPUT.REF_NUMBER=$REQUEST.refno\
$INPUT.AMOUNT=$REQUEST.amount
```

maka `PARSING_RULE` hanya terdiri dari 2 baris saja yaitu:

```ini
$INPUT.PRODUCT=$REQUEST.product_code\
$INPUT.ACCOUNT=$REQUEST.customer_no
```

karena setelah baris kehilangan `\` di akhirnya, maka simulator tidak akan melanjutkan pembacaan data. Cara konfigurasi ini berlaku untuk semua property.

## Path

**Property: `PATH`**

Universal REST Simulator akan memilih konfigurasi sesuai dengan `path` yang diakses dengan method yang sama dengan method request. Sebagai contoh: pengguna membuat 7 file konfigurasi, Universal REST Simulator akan memilih satu file dengan `path` yang sesuai. Setelah mendapatkan file dengan path dan method yang sesuai, simulator akan berhenti mencari file lain.

Contoh struktur file konfigurasi:

```
[root]
    [config]
        config1.txt
        config2.txt
        config3.txt
```

`root` adalah document root dari simulator
`config` adalah direktori di bawah document root
File `config1.txt`, `config2.txt`, `config3.txt` berada di dalam direktori `config`.

Jika `PATH` pada file `config1.txt` adalah `/biller/config1`, maka file tersebut akan dipilih hanya jika path pada URL request adalah `/biller/config1`.  Jika `PATH` pada file `config2.txt` adalah `/bank/config2`, maka file tersebut akan dipilih hanya jika path pada URL request adalah `/bank/config2`.  Jika tidak ada satu pun file dengan `path` yang cocok, maka simulator tidak akan memberikan response apa-apa.

## Method

**Property: `METHOD`**

Pengguna dapat memilih method `GET`, `POST` atau `PUT`. Universal REST Simulator akan membaca request sesuai dengan method yang digunakan pada konfigurasi dan akan mengabaikan request lain.

## Content Type

### Content Type Request

**Property: `REQUEST_TYPE`**

Universal REST Simulator  mendukung 3 macam `content type` yaitu sebagai berikut:

1. application/x-www-form-urlencoded
2. application/json
3. application/xml
4. application/soap+xml

`Content type` ini akan mempengaruhi cara menbaca request pada simulator.

### Content Type Response

**Property: `RESPONSE_TYPE`**

Pengguna bebas menggunakan content type apa saja untuk response karena pada dasarnya response simulator adalah teks murni.

## Konfigurasi Input

**Property: `PARSING_RULE`**

`$INPUT` adalah objek yang dapat dianggap sebagai global variable dan memiliki properti. `$INPUT` selalu ditulis dengan huruf kapital. Properti dari `$INPUT` dapat ditulis dengan huruf besar maupun huruf kecil dan akan bersifat _case sensitive_.

Input berasal dari 2 sumber yaitu `$REQUEST` (_request body_ pada `POST` dan `PUT` serta _query string_ pada `GET`) dan `$OUTPUT.HEADER` (request header). Baik `$REQUEST` maupun `$OUTPUT.HEADER` harus ditulis dengan huruf kapital. Nama properti dari `$REQUEST` adalah _case sensitive_ sedangkan nama properti dari `$OUTPUT.HEADER` berupa huruf kapital dan `-` diganti menjadi `_`. Hal ini disebabkan karena properti header mungkin sudah berubah dan tidak dapat diprediksi penulisannya secara pasti. 

`$REQUEST` dapat berasal dari:
1. Query pada `GET`
2. Request body pada `POST` dan `PUT`
3. Wildcard URL
4. Basic Authorization

Untuk mengambil `$REQUEST` dari wildcard URL, cukup menggunakan `{[IDENTIFIER]}` pada URL. `{[IDENTIFIER]}` bersifat _case sensitive_.

Contoh:

```ini
PATH=/payment/{[PRODUCT_CODE]}/{[CUSTOMER_ID]}/{[REFERENCE_NUMBER]}
```

Jika client melakukan request baik `GET`, `POST` atau `PUT` dengan URL `/payment/123/456/7890`, maka `$REQUEST.PRODUCT_CODE` akan bernila `123`, `$REQUEST.CUSTOMER_ID` akan bernila `456`, `$REQUEST.REFERENCE_NUMBER` akan bernila `7890`. Wildcard URL tetap dapat digabungkan dengan query string. Baik input dari wildcard URL maupun query string akan dapat diparsing dalam satu request.

Basic authorization mengandung username dan password untuk mengakses sebuah sumber data. Informasi username dan password dikodekan dengan base 64. Simulator mengekstrak informasi tersebut lalu menyimpannya ke objek `$AUTHORIZATION_BASIC`.

Untuk mengambil username dari basic authorization, gunakan `$AUTHORIZATION_BASIC.USERNAME`. Untuk mengambil password dari basic authorization, gunakan `$AUTHORIZATION_BASIC.PASSWORD`. Penulisan `$AUTHORIZATION_BASIC.USERNAME` dan `$AUTHORIZATION_BASIC.PASSWORD` harus dengan huruf kapital.

Simulator membaca input tergantung dari `content type` request. Untuk `content type`  `application/x-www-form-urlencoded`, simulator langsung mengambil nilai dari parameter yang sesuai. Untuk content type `application/json` dan `application/xml`, simulator akan mengambil data secara bertingkat. Dengan demikian, pengguna bebas memberikan request JSON dan XML dengan struktur bertingkat.

Matriks input dan method Universal REST Simulator adalah sebagai berikut:

| Method | Content Tpe                       | Sumber Data  | Alternatif Objek                |
| ------ | --------------------------------- | ------------ | --------------------- |
| `GET`  | applicatiom/x-www-form-urlencoded | Header, URL, <br>Basic Authorization, <br>GET  | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET` |
| `POST` | applicatiom/x-www-form-urlencoded | Header, Body, <br>Basic Authorization, <br>GET, POST | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET`, `$POST` |
| `POST` | applicatiom/json                  | Header, Body, <br>Basic Authorization, <br>GET | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET` |
| `POST` | applicatiom/xml                   | Header, Body, <br>Basic Authorization, <br>GET | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET` |
| `PUT`  | applicatiom/x-www-form-urlencoded | Header, Body, <br>Basic Authorization, <br>GET, PUT | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET`, `$PUT` |
| `PUT`  | applicatiom/json                  | Header, Body, <br>Basic Authorization, <br>GET | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET` |
| `PUT`  | applicatiom/xml                   | Header, Body, <br>Basic Authorization, <br>GET | `$HEADER`, `$REQUEST`, <br>`$AUTHORIZATION_BASIC`, <br>`$GET` |

**Input dari Object dan Array**

Pengguna mungkin menggunakan kombinasi antara `array` dan `object` sebagai `payload` dari `request` baik `GET`, `POST`, `PUT`, maupun `REQUEST`. Untuk mengambil nilai dari input yang kesemuanya adalah `object` dapat menggunakan operator titik (.) sedangkan untuk mengambil nilai dari input yang merupakan kombinasi antara `object` dan `array` dapat menggunakan operator kurung siku `[]`.

Contoh Payload

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

Contoh Konfigurasi

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

Operator `[]` dapat digunakan untuk mengambil nilai dari `object` dan `array`. Tidak diperkenankan menggabungkan operator `.` dan `[]` dalam mengambil sebuah nilai. Dengan demikian, penulisan `$INPUT.AMOUNT0=$REQUEST[items][0].amount` tidak diperbolehkan. Meskipun demikian, diperbolehkan menggunakan kombinasinya pada input yang berbeda.

Contoh Kombinasi Operator

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

`$INPUT.CUSTOMER_NAME=$REQUEST.customer.name` dapat pula ditulis dengan `$INPUT.CUSTOMER_NAME=$REQUEST[customer][name]` tanpa spasi sebelum `[` dan sesudah `]`.

**Nilai dari UUID**

Universal REST Simulator memungkinkan penggunaan UUID. Untuk mengambil UUID dari sistem, gunakan `$SYSTEM.UUID`. 

Contoh:

```ini
$INPUT.UUID=$SYSTEM.UUID\
$INPUT.RANDOM_ID=$SYSTEM.UUID\
$INPUT.UNIQ_ID=$SYSTEM.UUID
```
Dari contoh di atas, `$INPUT.UUID`, `$INPUT.RANDOM_ID`, dan `$INPUT.UNIQ_ID` akan memiliki nilai yang berbeda-beda.

**Contoh Konfigurasi Input URL Encoded**

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

Pada kunfigurasi di atas, `$INPUT.PRODUCT` akan mengambil nilai dari `$REQUEST.product_code`. Dengan demikian, saat pengguna melakukan request dengan URL `/biller/config1?product_code=10000&customer_no=081266612126&refno=5473248234`, maka `$INPUT.PRODUCT` akan bernilai `10000`, demikian pula `$INPUT.ACCOUNT` akan bernilai `081266612126` dan seterusnya.

**Contoh Konfigurasi Input JSON**

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

JSON biasanya digunakan pada method `POST` atau `PUT`. Pada kunfigurasi di atas, `$INPUT.PRODUCT` akan mengambil nilai dari `$REQUEST.data.destination_bank_code` yaitu `ROOT.data.destination_bank_code`. dengan `ROOT` adalah objek JSON. Dengan demikian, saat pengguna melakukan request dengan 

```http
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

maka `$INPUT.PRODUCT` akan bernilai `002`, demikian pula `$INPUT.ACCOUNT` akan bernilai `1234567890` dan seterusnya.

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

XML biasanya digunakan pada method `POST` atau `PUT`. Pada kunfigurasi di atas, `$INPUT.PRODUCT` akan mengambil nilai dari `$REQUEST.product_code` yaitu `ROOT.product_code`. dengan `ROOT` adalah tag level pertama dari XML. Dengan demikian, saat pengguna melakukan request dengan 

```http
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

maka `$INPUT.PRODUCT` akan bernilai `10001`, demikian pula `$INPUT.ACCOUNT` akan bernilai `081266612127` dan seterusnya.

## Kombinasi GET+POST dan GET+PUT

Universal REST Simulator dapat mengkombinasikan input `GET` dengan `POST` atau `PUT`. Untuk mengkombinasikan `GET` dengan `POST`, gunakan method `POST`. Untuk mengkombinasikan `GET` dengan `PUT`, gunakan method `PUT`.

`POST` dan `PUT` hanya berlaku untuk `REQUEST_TYPE=application/x-www-form-urlencoded` . Dalam hal ini, client juga harus mengirim `Content-type: application/x-www-form-urlencoded`. Pengambilan input dari `GET`, `POST`, dan `PUT` sama dengan `REQUEST` seperti contoh sebagai berikut:

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
Konfigurasi di atas menunjukkan bahwa path tersebut menghendaki method `POST` dan yang lain. Akan tetapi, pengguna tetap dapat mengambil nilai dari query pada `URL` menggunakan `$GET`.

> Contoh Request

```http
POST /universal-simulator/token?detail=yes HTTP/1.1 
Host: 127.0.0.1
Authorization: Basic dXNlcm5hbWU6cGFzc3dvcmQ=
Content-type: application/x-www-form-urlencoded
Content-length: 29

grant_type=client_credentials
```

Dari contoh di atas, input dari URL `/universal-simulator/token?detail=yes` diambil dengan `$GET.detail`. Nilai ini akan sama dengan `$REQUEST.detail` jika menggunakan method `GET`. Karena pada konfigurasi telah didefinisikan`METHOD=POST`, maka nilai ini hanya bisa diambil dengan `$GET.detail` karena `$REQUEST` hanya mengacu kepada request body yang dikirim.

Pengambilan data dari body dapat dilakukan dengan dua cara yaitu `$REQUEST` dan `$POST`. Ingat bahwa `$POST` hanya bisa digunakan jika `REQUEST_TYPE=application/x-www-form-urlencoded` dan `Content-type: application/x-www-form-urlencoded`. Untuk content type lain, harus menggunakan `$RQUEST`.

## Fungsi $CALC()

Fungsi `$CALC()` sangan berguna untuk melakukan operasi matematika di mana `$INPUT` menjadi salah satu operannya.

Sebagai contoh: pengguna akan menambahkan jumlah tagihan dengan admin fee. Jika tagihan disimpan di dalam variabel `$INPUT.AMOUNT` dan admin fee disimpan dalam variabel `$INPUT.FEE`, maka dapat ditulis dengan `$CALC($INPUT.AMOUNT + $INPUT.FEE)`. Jika admin fee adalah nilai tetap yaitu 2500, maka dapat ditulis dengan `$CALC($INPUT.AMOUNT + 2500)`.

Fungsi `$CALC()` juga dapat menghitung rumus dalam pasangan kurung. Contoh: `$CALC($INPUT.AMOUNT + $INPUT.FEE + ($INPUT.AMOUNT * 10/100))` dan sebagainya. Perhatikan bahwa jumlah kurung buka harus sama dengan jumlah kurung tutup. 

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

## Fungsi $DATE()

Fungsi `$DATE()` berguna untuk membuat tanggal dan jam secara otomatis. Tanggal dan jam akan mengikuti waktu server. Pengguna dapat menggunakan daerah waktu.

Format `$DATE()` mengikuti format pada bahasa pemrograman PHP. Berikut ini merupakan penjelasan dari format `$DATE()` pada bahasa pemrograman PHP. Untuk menyisipkan karakter konstan pada fungsi `$DATE()`, awali dengan `\`. Misalnya `$DATE('Y-m-d\TH:i:s.000\Z', 'UTC+7')` akan menampilkan `2020-10:10T20:20:20.000Z`. Perhatikan bahwa `\T` akan menjadi `T` dan `\Z` akan menjadi `Z`.

| format character  | Description | Example returned values |
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

## Fungsi $ISVALIDTOKEN()

Fungsi ini digunakan pada kondisi untuk memvalidasi token yang dikirimkan melalui `Authorization: Bearer`. Simulator akan mengambil token yang dikirimkan melalui header dengan key `Autorization`. Token ini kemudian akan divalidasi sesuai dengan konfigurasi server. Apabila token tersebut benar, `$ISVALIDTOKEN()` akan bernilai `true`. Sebaliknya, apabila token tersebut salah, `$ISVALIDTOKEN()` akan bernilai `false`. Simulator hanya akan memvalidasi token yang dibuat oleh simulator itu sendiri.

**Cara Membuat Token**

Agar simulator dapat membuat token, buatlah sebuah file konfigurasi. Output dari konfigurasi harus mengandung `$TOKEN.JWT`. Informasi lain seperti `$TOKEN.EXPIRE_AT` dan `$TOKEN.EXPIRE_IN` dapat pula ditambahkan.

**Konfigurasi Request Token**

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

**Cara Memvalidasi Token**

Untuk melakukan validasi token, caranya sangat mudah. Cukup dengan membuat kondisi `$ISVALIDTOKEN()`. Simulator akan mengambil token yang dikirim dan memvalidasi token tersebut. 

**Konfigurasi Validasi Token**

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

Konfigurasi di atas memperlihatkan contoh cara memvalidasi token. Perlu dicatat bahwa `$INPUT.USERNAME=$AUTHORIZATION_BASIC.USERNAME` dan `$INPUT.PASSWORD=$AUTHORIZATION_BASIC.PASSWORD` tidak dapat lagi digunakan karena key `Authorization` sudah digunakan untuk mengirimkan token. Meskipun demikian, Anda masih tetap dapat menggunakan key lain.

**Konfigurasi Validasi Token dengan Tambahahan Header**

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

## Pemilihan Kondisi

**Propety: `TRANSACTION_RULE`**

Simulator hanya mendukung kondisi `IF` dan tidak `ELSE`.  Semua data yang dihasilkan adalah data yang berada di antara `{[THEN]}` dan `{[ENDIF]}`. `IF` ditulis dengan `{[IF]}`, `THEN` ditulis dengan `{[THEN]}`, dan `ENDIF` ditulis dengan `{[ENDIF]}`. Hal ini untuk membedakan antara kata tercadang dengan kata yang mungkin muncul dalam data konfigurasi.

Simulator akan mengevaluasi ekspresi pada `{[IF]}`. Jika kondisi tersebut bernilai `true`, maka simulator akan mengambil semua data pada blok tersebut tidak peduli apakah kondisi pada blok berikutnya bernilai `true` atau `false`.

Beberapa data yang yang dapat dihasikan oleh simulator adalah sebagai berikut:


1. `$OUTPUT.STATUS` adalah HTTP status code. Nilai default dari `$OUTPUT.STATUS` adalah `200`
2. `$OUTPUT.HEADER` adalah response header yang dibuat perbaris.
3. `$OUTPUT.DELAY` adalah waktu tunggu. Server akan menunggu beberapa saat sebelum mengirimkan respon.
4. `$OUTPUT.BODY` adalah response body. 
5. `$OUTPUT.CALLBACK_URL` adalah URL yang dituju pada proses callback.
6. `$OUTPUT.CALLBACK_METHOD` adalah method dari callback. Method yang dapat digunakan adalah `GET`, `POST`, dan `PUT`.
7. `$OUTPUT.CALLBACK_TYPE` adalah content type untuk callback. Content type ini bebas sesuai kebutuhan.
8. `$OUTPUT.CALLBACK_TIMEOUT` adalah timeout untuk callback.
9. `$OUTPUT.CALLBACK_HEADER` adalah request header untuk callback.
10. `$OUTPUT.CALLBACK_BODY` adalah request body untuk callback.

Penjelasan tentang callback dapat dibaca pada bagian **Callback**.

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

Simulator akan mengevaluasi kondisi yang sesuai. Jika ada dua buah kondisi

## HTTP Status

Universal REST Simulator memungkinkan penggunaan HTTP Status tidak standard. HTTP Status Standard dapat dilihat di https://developer.mozilla.org/id/docs/Web/HTTP/Status

Untuk HTTP Status standard, pengguna cukup menuliskan kodenya saja. Misalnya `$OUTPUT.STATUS=200` atau `$OUTPUT.STATUS=403`. Untuk penggunaan HTTP Status tidak standard, pengguna perlu menambahkan teks keterangan di belakang kode. Sebagai contoh: `$OUTPUT.STATUS=699 Under Maintenance`.  Jika pengguna tidak menambahkan keterangan di belakang kode, maka Universal REST Simulator akan mengirimkan HTTP Status 500 atau Internal Server Error.   

## Contoh Konfigurasi Request URL-Encoded

> Contoh Request

```http
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

## Contoh Konfigurasi Request JSON

Pada reqquest JSON, mungkin `key` dari JSON mengandung karakter selain `alpha numeric` dan `underscore`. Agar simulator dapat membaca input dari request tersebut, ganti semua karakter selain `alpha numeric` dan `underscore` dengan `underscore` atau `_`. 

> Contoh Request

```http
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

## Contoh Konfigurasi Request XML

> Contoh Request

```http
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

**Konfigurasi**

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

Beberapa simulator mengirimkan callback dikarenakan proses asinkron. Universal REST Simulator juga mendukung callback. Callback akan dikirim setelah request diparsing sebelum melakukan delay (jika ada).

Untuk menambahkan callback, beberapa konfigurasi perlu dibuat pada `TRANSACTION_RULE` yaitu sebagai berikut:

1. `$OUTPUT.CALLBACK_URL` adalah URL yang dituju pada proses callback.
2. `$OUTPUT.CALLBACK_METHOD` adalah method dari callback. Method yang dapat digunakan adalah `GET`, `POST`, dan `PUT`.
3. `$OUTPUT.CALLBACK_TYPE` adalah content type untuk callback. Content type ini bebas sesuai kebutuhan.
4. `$OUTPUT.CALLBACK_HEADER` adalah request header untuk callback.
5. `$OUTPUT.CALLBACK_BODY` adalah request body untuk callback.

Method default adalah `GET`. Apabila `$OUTPUT.CALLBACK_METHOD` adalah `GET`, maka `$OUTPUT.CALLBACK_BODY` tidak akan dikirim dan `Content-length` dan `Content-type` pada header juga tidak akan dikirim.

Perlu diingat bahwa pada method `POST` dan `PUT`, pengguna wajib menjantumkan `$OUTPUT.CALLBACK_BODY` karena server yang dituju akan menunggu simulator mengirimkan `body` pada proses callback. `$OUTPUT.CALLBACK_TYPE` pada `POST` dan `PUT` juga harus diseting untuk menentukan `Content-type` pada callback. `Content-length` akan dibuat secara otomatis oleh simulator pada saat callback dilakukan. Pengguna dapat menambahkan `User-agent` pada header. Jika pengguna tidak memasukkan `User-agent`, maka simulator akan membuat `User-agent` default karena beberapa server mungkin mewajibkan setiap request mencantumkan `User-agent`.

**Konfigurasi**

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
$OUTPUT.CALLBACK_HEADER=\X-Server-Name: Universal REST Simulator\
X-Response-Code: 00\
X-Response-Text: Success\
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
$OUTPUT.HEADER=\X-Server-Name: Universal REST Simulator\
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

## File Manager

Universal REST Simulator dilengkapi dengan file manager untuk membuat, mengubah dan menghapus file konfigurasi. File manager tersebut dilengkapi dengan username dan password untuk masuk ke dalamnya. File manager dapat diakses dengan path `/filemanager/` dari document root simulator.

Username dan password file manager disimpan di dalam file `.htpasswd` dalam direktori `filemanager`. Username dan password dapat ditambah dengan cara menambahkan baris di bagian akhir file tersebut. Metode enkripsi password yang didukung adalah:

1.  SHA dengan awalan `{SHA}`
2. APR1 dengan awalan `$apr1$`

Username dan password dapat dibuat dengan menggunakan Htpasswd Generator yang banyak tersedia secara online.

Panduan lengkap untuk menggunakan file manager dapat dilihat di halaman https://github.com/kamshory/PlanetbiruFileManager

## Check Path

Universal REST Simulator menggunakan `path` dan `method` untuk menentukan konfigurasi mana yang akan digunakan. Apabila terdapat dua atau lebih pasangan `path` dan `method` yang sama, maka file konfigurasi yang pertama kali ditemukan yang akan digunakan. 

Pengguna perlu mengetahui apakah `path` dan `method` tersebut sudah digunakan atau belum atau apakah terjadi duplikasi atau tidak. Untuk memeriksa `path` dan `method` yang digunakan, pengguna cukup mengakses /checkpath/ dari document root simulator menggunakan browser.

