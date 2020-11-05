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

Content type request yang dapat digunakan adalah `application/x-www-form-urlencoded` atau `application/json`. Content type ini akan mempengaruhi dara menbaca request pada simulator.

### Content Type Response

**Property: `RESPONSE_TYPE`**

Pengguna bebas menggunakan content type apa saja untuk response karena pada dasarnya response simulator adalah teks murni.

## Konfigurasi Input

**Property: `PARSING_RULE`**

Simulator membaca input tergantung dari content type request. Untuk content type  `application/x-www-form-urlencoded`, simulator langsung mengambil nilai dari parameter yang sesuai. Untuk content type `application/json`, simulator akan mengambil data secara bertingkat. Dengan demikian, pengguna bebas memberikan request JSON dengan struktur bertingkat.

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

maka `$INPUT.PRODUCT` akan bernilai `002`, demikian pula `$INPUT.ACCOUNT` akan bernilai `beneficiary_account_number` dan seterusnya.

## Pemilihan Kondisi

**Propety: `TRANSACTION_RULE`**

Pada dasarnya simulator hanya akan menghasilkan `DELAY` dan `OUTPUT`. `DELAY` adalah berapa lama simulator akan menunggu sebelum melanjutkan pproses. `DELAY` sangat berguna untuk kasus `time out`. `OUTPUT` adalah response body yang akan dikirimkan ke klien.

Simulator hanya mendukung kondisi `IF` dan tidak `ELSE`. Baik `DELAY` maupun `OUTPUT` yang dihasilkan adalah data yang berada di antara `THEN` dan `ENDIF`.

Simulator akan mengevaluasi ekspresi pada `IF`. Jika kondisi tersebut bernilai `true`, maka simulator akan mengambil `DELAY` dan `OUTPUT` pada blok tersebut tidak peduli apakah kondisi pada blok berikutnya bernilai `true` atau `false`.

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
IF ($INPUT.COMMAND == "inquiry" && $INPUT.PRODUCT == "002" && $INPUT.ACCOUNT == "1234567890")\
THEN $DELAY=0\
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
ENDIF\
IF ($INPUT.COMMAND == "inquiry" && $INPUT.PRODUCT == "002" && $INPUT.ACCOUNT == "1234567891")\
THEN $DELAY=20000\
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
ENDIF\
IF (true)\
THEN $DELAY=0\
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
ENDIF\
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

**Configurasi**

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
IF ($INPUT.PRODUCT == "10000" && $INPUT.ACCOUNT == "081266612126")\
THEN $DELAY=0\
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
ENDIF\
IF ($INPUT.PRODUCT == "10000" && $INPUT.ACCOUNT == "081266612127")\
THEN $DELAY=20000\
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
ENDIF\
IF (true)\
THEN $DELAY=0\
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
ENDIF\
```

## Contoh Konfigurasi Request JSON

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

**Configurasi**

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
IF ($INPUT.COMMAND == "inquiry" && $INPUT.PRODUCT == "002" && $INPUT.ACCOUNT == "1234567890")\
THEN $DELAY=0\
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
ENDIF\
IF ($INPUT.COMMAND == "inquiry" && $INPUT.PRODUCT == "002" && $INPUT.ACCOUNT == "1234567891")\
THEN $DELAY=20000\
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
ENDIF\
IF (true)\
THEN $DELAY=0\
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
ENDIF\
```
