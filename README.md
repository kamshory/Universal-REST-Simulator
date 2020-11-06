# Universal REST Simulator

## Pengenalan

Universal REST Simulator adalah simulator universal untuk REST API. Simulator ini dapat dikonfigurasi dengan menambahkan beberapa file konfigurasi ke dalam direktori `config`. Universal REST Simulator dapat membaca semua ekstensi file.

Konfigurasi mirip dengan file `ini`. Apabila nilai konfigurasi lebih dari satu baris, maka nilai pada baris selain terakhir harus diakhiri dengan `\` (backslash).

Contoh:

```ini
PARSING_RULE=\
$INPUT.PRODUCT=$REQUEST.product_code\
$INPUT.ACCOUNT=$REQUEST.customer_no\
$INPUT.REF_NUMBER=$REQUEST.refno\
$INPUT.ACCEPT_LANGUAGE=$HEADER.ACCEPT_LANGUAGE\
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

Universal REST Simulator akan memilih konfigurasi sesuai dengan `path` yang diakses. Sebagai contoh: pengguna membuat 7 file konfigurasi, Universal REST Simulator akan memilih satu file dengan `path` yang sesuai. Setelah mendapatkan file yang sesuai, simulator akan berhenti mencari file lain.

Contoh struktur file konfigurasi:

```
[root]
    [config]
        mai.txt
        bni.txt
        mandiri.txt
```

`root` adalah document root dari simulator
`config` adalah direktori di bawah document root
File `mai.txt`, `bni.txt`, `mandiri.txt` berada di dalam direktori `config`.

Jika `PATH` pada file `mai.txt` adalah `/biller/mai`, maka file tersebut akan dipilih hanya jika path pada URL request adalah `/biller/mai`.  Jika `PATH` pada file `bni.txt` adalah `/bank/bni`, maka file tersebut akan dipilih hanya jika path pada URL request adalah `/bank/bni`.  Jika tidak ada satu pun file dengan `path` yang cocok, maka simulator tidak akan memberikan response apa-apa.

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

`Content type` ini akan mempengaruhi cara menbaca request pada simulator.

### Content Type Response

**Property: `RESPONSE_TYPE`**

Pengguna bebas menggunakan content type apa saja untuk response karena pada dasarnya response simulator adalah teks murni.

## Konfigurasi Input

**Property: `PARSING_RULE`**

`$INPUT` adalah objek yang dapat dianggap sebagai global variable dan memiliki properti. `$INPUT` selalu ditulis dengan huruf kapital. Properti dari `$INPUT` dapat ditulis dengan huruf besar maupun huruf kecil dan akan bersifat _case sensitive_.

Input berasal dari 2 sumber yaitu `$REQUEST` (_request body_ pada `POST` dan `PUT` serta _query string_ pada `GET`) dan `$HEADER` (request header). Baik `$REQUEST` maupun `$HEADER` harus ditulis dengan huruf kapital. Nama properti dari `$REQUEST` adalah _case sensitive_ sedangkan nama properti dari `$HEADER` berupa huruf kapital dan `-` diganti menjadi `_`. Hal ini disebabkan karena properti header mungkin sudah berubah dan tidak dapat diprediksi penulisannya secara pasti. 

Simulator membaca input tergantung dari `content type` request. Untuk `content type`  `application/x-www-form-urlencoded`, simulator langsung mengambil nilai dari parameter yang sesuai. Untuk content type `application/json`, simulator akan mengambil data secara bertingkat. Dengan demikian, pengguna bebas memberikan request JSON dengan struktur bertingkat.

Matriks input dan method Universal REST Simulator adalah sebagai berikut:

| Method | Content Tpe                       | Sumber Data  | Objek                 |
| ------ | --------------------------------- | ------------ | --------------------- |
| `GET`  | applicatiom/x-www-form-urlencoded | Header, URL  |` $HEADER`, `$REQUEST` |
| `POST` | applicatiom/x-www-form-urlencoded | Header, Body |` $HEADER`, `$REQUEST` |
| `POST` | applicatiom/json                  | Header, Body |` $HEADER`, `$REQUEST` |
| `POST` | applicatiom/xml                   | Header, Body |` $HEADER`, `$REQUEST` |
| `PUT`  | applicatiom/x-www-form-urlencoded | Header, Body |` $HEADER`, `$REQUEST` |
| `PUT`  | applicatiom/json                  | Header, Body |` $HEADER`, `$REQUEST` |
| `PUT`  | applicatiom/xml                   | Header, Body |` $HEADER`, `$REQUEST` |


**Contoh Konfigurasi Input URL Encoded**

```ini
PATH=/biller/mai
METHOD=POST
REQUEST_TYPE=application/x-www-form-urlencoded
RESPONSE_TYPE=application/json
PARSING_RULE=\
$INPUT.PRODUCT=$REQUEST.product_code\
$INPUT.ACCOUNT=$REQUEST.customer_no\
$INPUT.REF_NUMBER=$REQUEST.refno
```

Pada kunfigurasi di atas, `$INPUT.PRODUCT` akan mengambil nilai dari `$REQUEST.product_code`. Dengan demikian, saat pengguna melakukan request dengan URL `/biller/mai?product_code=10000&customer_no=081266612126&refno=5473248234`, maka `$INPUT.PRODUCT` akan bernilai `10000`, demikian pula `$INPUT.ACCOUNT` akan bernilai `081266612126` dan seterusnya.

**Contoh Konfigurasi Input JSON**

```ini
PATH=/bank/bni
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
POST /bank/bni HTTP/1.1 
Host: 10.16.1.235
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
PATH=/bank/mandiri
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
POST /bank/mandiri HTTP/1.1 
Host: 10.16.1.235
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

## Format $DATE()

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

## Pemilihan Kondisi

**Propety: `TRANSACTION_RULE`**

Simulator hanya mendukung kondisi `IF` dan tidak `ELSE`.  Semua data yang dihasilkan adalah data yang berada di antara `{[THEN]}` dan `{[ENDIF]}`. `IF` ditulis dengan `{[IF]}`, `THEN` ditulis dengan `{[THEN]}`, dan `ENDIF` ditulis dengan `{[ENDIF]}`. Hal ini untuk membedakan antara kata tercadang dengan kata yang mungkin muncul dalam data konfigurasi.

Simulator akan mengevaluasi ekspresi pada `{[IF]}`. Jika kondisi tersebut bernilai `true`, maka simulator akan mengambil semua data pada blok tersebut tidak peduli apakah kondisi pada blok berikutnya bernilai `true` atau `false`.

Beberapa data yang yang dapat dihasikan oleh simulator adalah sebagai berikut:

1. `$HEADER` adalah response header yang dibuat perbaris.
2. `$DELAY` adalah waktu tunggu. Server akan menunggu beberapa saat sebelum mengirimkan respon.
3. `$OUTPUT` adalah response body. 
4. `$CALLBACK_URL` adalah URL yang dituju pada proses callback.
5. `$CALLBACK_METHOD` adalah method dari callback. Method yang dapat digunakan adalah `GET`, `POST`, dan `PUT`.
6. `$CALLBACK_TYPE` adalah content type untuk callback. Content type ini bebas sesuai kebutuhan.
7. `$CALLBACK_HEADER` adalah request header untuk callback.
8. `$CALLBACK_OUTPUT` adalah request body untuk callback.

Penjelasan tentang callback dapat dibaca pada bagian **Callback**.

```ini
PATH=/bank/bni
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
{[THEN]} $DELAY=0\
$OUTPUT=\
{\
	"rc":"00",\
	"sn":"82634862385235365285",\
	"nama":"BNI",\
	"customer_no":"$INPUT.ACCOUNT",\
	"product_code":"$INPUT.PRODUCT",\
	"time_stamp":"$DATE('j F Y H:i:s', 'UTC+7')",\
	"msg":"Transaksi ini dikenakan biaya Rp. 250",\
	"refid":"$INPUT.REF_NUMBER"\
}\
{[ENDIF]}\
{[IF]} ($INPUT.COMMAND == "inquiry" && $INPUT.PRODUCT == "002" && $INPUT.ACCOUNT == "1234567891")\
{[THEN]} $DELAY=20000\
$OUTPUT=\
{\
	"rc":"00",\
	"sn":"82634862385235365285",\
	"nama":"Budi",\
	"customer_no":$INPUT.ACCOUNT,\
	"product_code":$INPUT.PRODUCT,\
	"time_stamp":"$DATE('Y-m-d H:i:s')",\
	"msg":"Transaksi ini dikenakan biaya Rp. 250",\
	"refid":"873264832658723585"\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]} $DELAY=0\
$OUTPUT=\
{\
	"rc":"25",\
	"sn":"82634862385235365285",\
	"nama":"Budi",\
	"customer_no":$INPUT.ACCOUNT,\
	"product_code":$INPUT.PRODUCT,\
	"time_stamp":"$DATE('Y-m-d H:i:s')",\
	"msg":"Transaksi ini dikenakan biaya Rp. 250",\
	"refid":"873264832658723585"\
}\
{[ENDIF]}\
```

Simulator akan mengevaluasi kondisi yang sesuai. Jika ada dua buah kondisi

## Contoh Konfigurasi Request URL-Encoded

> Contoh Request

```http
GET /biller/mai?product_code=10000&customer_no=081266612126&refno=5473248234 HTTP/1.1 
Host: 10.16.1.235
Content-type: application/json
Content-length: 166
```

**Konfigurasi**

```ini
PATH=/biller/mai
METHOD=POST
REQUEST_TYPE=application/x-www-form-urlencoded
RESPONSE_TYPE=application/json
PARSING_RULE=\
$INPUT.PRODUCT=$REQUEST.product_code\
$INPUT.ACCOUNT=$REQUEST.customer_no\
$INPUT.REF_NUMBER=$REQUEST.refno
TRANSACTION_RULE=\
{[IF]} ($INPUT.PRODUCT == "10000" && $INPUT.ACCOUNT == "081266612126")\
{[THEN]} $DELAY=0\
$OUTPUT=\
{\
	"rc":"00",\
	"sn":"82634862385235365285",\
	"nama":"BNI",\
	"customer_no":"$INPUT.ACCOUNT",\
	"product_code":"$INPUT.PRODUCT",\
	"time_stamp":"$DATE('j F Y H:i:s', 'UTC+7')",\
	"msg":"Transaksi ini dikenakan biaya Rp. 250",\
	"refid":"$INPUT.REF_NUMBER"\
}\
{[ENDIF]}\
{[IF]} ($INPUT.PRODUCT == "10000" && $INPUT.ACCOUNT == "081266612127")\
{[THEN]} $DELAY=20000\
$OUTPUT=\
{\
	"rc":"00",\
	"sn":"82634862385235365285",\
	"nama":"Budi",\
	"customer_no":"$INPUT.ACCOUNT",\
	"product_code":"$INPUT.PRODUCT",\
	"time_stamp":"$DATE('Y-m-d H:i:s')",\
	"msg":"Transaksi ini dikenakan biaya Rp. 250",\
	"refid":"873264832658723585"\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]} $DELAY=0\
$OUTPUT=\
{\
	"rc":"25",\
	"sn":"82634862385235365285",\
	"nama":"Budi",\
	"customer_no":"$INPUT.ACCOUNT",\
	"product_code":"$INPUT.PRODUCT",\
	"time_stamp":"$DATE('Y-m-d H:i:s')",\
	"msg":"Transaksi ini dikenakan biaya Rp. 250",\
	"refid":"873264832658723585"\
}\
{[ENDIF]}\
```

## Contoh Konfigurasi Request JSON

Pada reqquest JSON, mungkin `key` dari JSON mengandung karakter selain `alpha numeric` dan `underscore`. Agar simulator dapat membaca input dari request tersebut, ganti semua karakter selain `alpha numeric` dan `underscore` dengan `underscore` atau `_`. 

> Contoh Request

```http
POST /bank/bni HTTP/1.1 
Host: 10.16.1.235
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

**Konfigurasi**

```ini
PATH=/bank/bni
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
{[THEN]} $DELAY=0\
$OUTPUT=\
{\
	"rc":"00",\
	"sn":"82634862385235365285",\
	"nama":"BNI",\
	"customer_no":"$INPUT.ACCOUNT",\
	"product_code":"$INPUT.PRODUCT",\
	"time_stamp":"$DATE('j F Y H:i:s', 'UTC+7')",\
	"msg":"Transaksi ini dikenakan biaya Rp. 250",\
	"refid":"$INPUT.REF_NUMBER"\
}\
{[ENDIF]}\
{[IF]} ($INPUT.COMMAND == "inquiry" && $INPUT.PRODUCT == "002" && $INPUT.ACCOUNT == "1234567891")\
{[THEN]} $DELAY=20000\
$OUTPUT=\
{\
	"rc":"00",\
	"sn":"82634862385235365285",\
	"nama":"Budi",\
	"customer_no":"$INPUT.ACCOUNT",\
	"product_code":"$INPUT.PRODUCT",\
	"time_stamp":"$DATE('Y-m-d H:i:s')",\
	"msg":"Transaksi ini dikenakan biaya Rp. 250",\
	"refid":"873264832658723585"\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]} $DELAY=0\
$OUTPUT=\
{\
	"rc":"25",\
	"sn":"82634862385235365285",\
	"nama":"Budi",\
	"customer_no":"$INPUT.ACCOUNT",\
	"product_code":"$INPUT.PRODUCT",\
	"time_stamp":"$DATE('Y-m-d H:i:s')",\
	"msg":"Transaksi ini dikenakan biaya Rp. 250",\
	"refid":"873264832658723585"\
}\
{[ENDIF]}\
```

## Contoh Konfigurasi Request XML

> Contoh Request

```http
POST /bank/mandiri HTTP/1.1 
Host: 10.16.1.235
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
PATH=/bank/mandiri

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
{[THEN]} $DELAY=0\
$OUTPUT=\<?xml version="1.0" encoding="UTF-8"?>\
<data>\
\
	<rc>00</rc>\
	<sn>82634862385235365285</sn>\
	<nama>BNI</nama>\
	<customer_no>$INPUT.ACCOUNT</customer_no>\
	<product_code>$INPUT.PRODUCT</product_code>\
	<time_stamp>$DATE('j F Y H:i:s', 'UTC+7')</time_stamp>\
	<msg>Transaksi ini dikenakan biaya Rp. 250</msg>\
	<refid>$INPUT.REF_NUMBER</refid>\
<data>\
{[ENDIF]}\
{[IF]} ($INPUT.PRODUCT == "10001" && $INPUT.ACCOUNT == "081266612127")\
{[THEN]} $DELAY=0\
$OUTPUT=\<?xml version="1.0" encoding="UTF-8"?>\
<data>\
\
	<rc>00</rc>\
	<sn>82634862385235365285</sn>\
	<nama>BNI</nama>\
	<customer_no>$INPUT.ACCOUNT</customer_no>\
	<product_code>$INPUT.PRODUCT</product_code>\
	<time_stamp>$DATE('j F Y H:i:s', 'UTC+7')</time_stamp>\
	<msg>Transaksi ini dikenakan biaya Rp. 250</msg>\
	<refid>$INPUT.REF_NUMBER</refid>\
<data>\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]} $DELAY=0\
$OUTPUT=\<?xml version="1.0" encoding="UTF-8"?>\
<data>\
\
	<rc>25</rc>\
	<sn>82634862385235365285</sn>\
	<nama>BNI</nama>\
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

1. `$CALLBACK_URL` adalah URL yang dituju pada proses callback.
2. `$CALLBACK_METHOD` adalah method dari callback. Method yang dapat digunakan adalah `GET`, `POST`, dan `PUT`.
3. `$CALLBACK_TYPE` adalah content type untuk callback. Content type ini bebas sesuai kebutuhan.
4. `$CALLBACK_HEADER` adalah request header untuk callback.
5. `$CALLBACK_OUTPUT` adalah request body untuk callback.

Method default adalah `GET`. Apabila `$CALLBACK_METHOD` adalah `GET`, maka `$CALLBACK_OUTPUT` tidak akan dikirim dan `Content-length` dan `Content-type` pada header juga tidak akan dikirim.

Perlu diingat bahwa pada method `POST` dan `PUT`, pengguna wajib menjantumkan `$CALLBACK_OUTPUT` karena server yang dituju akan menunggu simulator mengirimkan `body` pada proses callback. `$CALLBACK_TYPE` pada `POST` dan `PUT` juga harus diseting untuk menentukan `Content-type` pada callback. `Content-length` akan dibuat secara otomatis oleh simulator pada saat callback dilakukan. Pengguna dapat menambahkan `User-agent` pada header. Jika pengguna tidak memasukkan `User-agent`, maka simulator akan membuat `User-agent` default karena beberapa server mungkin mewajibkan setiap request mencantumkan `User-agent`.

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
$DELAY=0\
$CALLBACK_URL=http://localhost/test/\
$CALLBACK_METHOD=POST\
$CALLBACK_TYPE=application/xml\
$CALLBACK_HEADER=\X-Server-Name: Universal REST Simulator\
X-Response-Code: 00\
X-Response-Text: Success\
$CALLBACK_OUTPUT=<?xml version="1.0" encoding="UTF-8"?>\
<data>\
\
	<rc>00</rc>\
	<sn>82634862385235365285</sn>\
	<nama>BNI</nama>\
	<customer_no>$INPUT.ACCOUNT</customer_no>\
	<product_code>$INPUT.PRODUCT</product_code>\
	<time_stamp>$DATE('j F Y H:i:s', 'UTC+7')</time_stamp>\
	<msg>Ini output dari callback Transaksi ini dikenakan biaya Rp. 250</msg>\
	<refid>$INPUT.REF_NUMBER</refid>\
<data>\
$HEADER=\X-Server-Name: Universal REST Simulator\
X-Response-Code: 00\
X-Response-Text: Success\
$OUTPUT=<?xml version="1.0" encoding="UTF-8"?>\
<data>\
\
	<rc>00</rc>\
	<sn>82634862385235365285</sn>\
	<nama>BNI</nama>\
	<customer_no>$INPUT.ACCOUNT</customer_no>\
	<product_code>$INPUT.PRODUCT</product_code>\
	<time_stamp>$DATE('j F Y H:i:s', 'UTC+7')</time_stamp>\
	<msg>Transaksi ini dikenakan biaya Rp. 250</msg>\
	<refid>$INPUT.REF_NUMBER</refid>\
<data>\
{[ENDIF]}\
{[IF]} ($INPUT.PRODUCT == "10001" && $INPUT.ACCOUNT == "081266612127")\
{[THEN]} $DELAY=0\
$OUTPUT=<?xml version="1.0" encoding="UTF-8"?>\
<data>\
\
	<rc>00</rc>\
	<sn>82634862385235365285</sn>\
	<nama>BNI</nama>\
	<customer_no>$INPUT.ACCOUNT</customer_no>\
	<product_code>$INPUT.PRODUCT</product_code>\
	<time_stamp>$DATE('j F Y H:i:s', 'UTC+7')</time_stamp>\
	<msg>Transaksi ini dikenakan biaya Rp. 250</msg>\
	<refid>$INPUT.REF_NUMBER</refid>\
<data>\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]} $DELAY=0\
$OUTPUT=<?xml version="1.0" encoding="UTF-8"?>\
<data>\
\
	<rc>25</rc>\
	<sn>82634862385235365285</sn>\
	<nama>BNI</nama>\
	<customer_no>$INPUT.ACCOUNT</customer_no>\
	<product_code>$INPUT.PRODUCT</product_code>\
	<time_stamp>$DATE('j F Y H:i:s', 'UTC+7')</time_stamp>\
	<msg>Pelanggan tidak ditemukan</msg>\
	<refid>$INPUT.REF_NUMBER</refid>\
<data>\
{[ENDIF]}\
```
