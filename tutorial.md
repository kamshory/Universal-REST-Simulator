
# Tutorial Menggunakan Universal REST Simulator

## Daftar Isi

1. [Sekilas Tentang Universal REST Simulator](#sekilas-tentang-universal-rest-simulator)
2. [File Manager](#file-manager)
3. [Check Path](#check-path)
4. [File Konfigurasi](#file-konfigurasi)
5. [Simulator Sederhana GET application/x-www-form-urlencoded](#simulator-sederhana-get-applicationx-www-form-urlencoded)
6. [Simulator Sederhana POST application/x-www-form-urlencoded](#simulator-sederhana-post-applicationx-www-form-urlencoded)
7. [Simulator Sederhana PUT application/x-www-form-urlencoded](#simulator-sederhana-put-applicationx-www-form-urlencoded)
8. [Simulator Sederhana POST application/json](#simulator-sederhana-post-applicationjson)
9. [Simulator Sederhana PUT application/json](#simulator-sederhana-put-applicationjson)
10. [Simulator Sederhana POST application/xml](#simulator-sederhana-post-applicationxml)
11. [Simulator Sederhana PUT application/xml](#simulator-sederhana-post-applicationxml)
12. [Kombinasi GET dan POST](#kombinasi-get-dan-post)
13. [Kombinasi GET dan PUT](#kombinasi-get-dan-put)
14. [Simulator Sederhana Input dari URL](#simulator-sederhana-input-dari-url)
15. [Simulator Sederhana Input dari Header](#simulator-sederhana-input-dari-header)
16. [Simulator Sederhana Input dari Basic Authorization](#simulator-sederhana-input-dari-basic-authorization)
17. [Simulator Sederhana Output UUID](#simulator-sederhana-output-uuid)
18. [Simulator Sederhana Input Array](#simulator-sederhana-input-array)
19. [Simulator Sederhana Input Object dan Array](#simulator-sederhana-input-object-dan-array)
20. [HTTP Status Standard](#http-status-standard)
21. [HTTP Status Non-Standard](#http-status-non-standard)
22. [Membuat Token](#membuat-token)
23. [Memvalidasi Token](#memvalidasi-token)
24. [Membuat Callback](#membuat-callback)
25. [Menggunakan Delay](#menggunakan-delay)
26. [Fungsi $DATE()](#fungsi-date)
27. [Fungsi $CALC()](#fungsi-calc)
28. [Fungsi $ISVALIDTOKEN()](#fungsi-isvalidtoken)
29. [Fungsi $NUMBERFORMAT()](#fungsi-numberformat)

## Sekilas Tentang Universal REST Simulator

Universal REST Simulator adalah simulator REST untuk membuat simulator server aplikasi. Simulator ini akan mensimulasikan respon dari sebuah sistem saat diberi request tertentu. Universal REST Simulator menggunakan protokol HTTP dengan method `GET`, `POST` dan `PUT` dengan tipe request `x-www-form-urlencode`, `JSON` dan `XML`. Tipe respon dapat berupa `text`, `HTML`, `XML`, `JSON` maupun `CSV`.

Universal REST Simulator mengambil input dari request klien melalui beberapa cara yaitu sebagai berikut:

1. `$HEADER` yaitu request header dengan  nama header yang eksplisit
2. `$AUTHORIZATION_BASIC` yaitu username dan password pada basic authentication
3. `$URL` yaitu nilai yang cocok dari pola URL dengan membandingkan antara URL pada file konfigurasi dengan URL pada request dari klien
4. `$GET` yaitu nilai yang diambil dari parameter yang dikirimkan melalui URL dengan pengkodean `x-www-form-urlencode`
5. `$POST` yaitu nilai yang diambil dari body request dengan method `POST` dengan pengkodean `x-www-form-urlencode`
6. `$PUT` yaitu nilai yang diambil dari body request dengan method `PUT` dengan pengkodean `x-www-form-urlencode`
7. `$REQUEST` tergantung dari method yang digunakan pada file konfigurasi. Berbeda dengan `$GET`, `$PUT` dan `$POST` yang hanya mendukung `x-www-form-urlencode`, `$REQUEST` mendukung *content type* `x-www-form-urlencode`, JSON dan XML baik objek maupun array.

Output dari Universal REST Simulator adalah HTTP Status, header respon dan body respon.

Universal REST Simulator dilengkapi dengan callback sehingga dapat mengirimkan request ke endpoint tertentu dengan kondisi tertentu sesuai dengan konfigurasi yang dibuat. Pengguna juga dapat mengatur request time out callback.

## File Manager

File manager pada Universal REST Simulator digunakan utuk membuat, mengubah dan mengatur file konfigurasi simulator. Untuk dapat membuat dan mengatur file konfigurasi simulator, pengguna harus login ke file manager. Username dan password pengguna disimpan dalam file .htpasswd yang disimpan di direktori filemanager di dalam direktori simulator.

Untuk mengakses file manager, buka Universal REST Simulator dengan menggunakan browser web dan masukkan path `/filemanager/` relatif terhadap path Universal REST Simulator.

## Check Path

Check path digunakan untuk melihat path dan method yang ada pada semua file konfigurasi. Tujuan dari check path adalah sebagai berikut:

1. menghindari konflik path dan method
2. memudahkan dalam pencarian file konfigurasi untuk keperluan perubahan dan pembaruan
3. jalan pintas untuk mengubah dan memperbarui file konfigurasi

Untuk mengakses check path, buka Universal REST Simulator dengan menggunakan browser web dan masukkan path `/checkpath/` relatif terhadap path Universal REST Simulator.

## File Konfigurasi

Konfigurasi simulator diatur oleh file-file yang disimpan di dalam direktori `/config` relatif terhadap direktori Universal REST Simulator. *Working directory* file manager adalah `/config` sehingga pengguna cukup membuat file konfigurasi pada `base directory` tanpa memasukkannya ke dalam direktori lagi. 

```txt
[root]
    [config]
        config1.ini
        config2.ini
        config3.ini
```

File yang berada di dalam direktori (tidak di `base directory`) tidak akan dibaca oleh simulator. Dengan demikian, apabila pengguna ingin mengarsipkan konfigurasi yang sudah tidak digunakan lagi cukup membuat sebuah direktori dan memasukkan file-file konfigurasi yang tidak digunakan ke dalam direktori tersebut.

Pada saat simulator menerima request dari klien, simulator akan mencari file konfigurasi yang cocok dengan method dan path dari request yang diterima. Apabila simulator menemukan file yang sesuai, maka simulator akan berhenti mencari file dan menggunakan konfigurasi pada file yang tersebut. File konfigurasi yang tidak dapat diparsing dengan benar akan diabaikan dan tidak akan menyebabkan kerusakan pada simulator.

File konfigurasi Universal REST Simulator dapat memiliki ekstensi apapun. Akan tetapi, untuk memudahkan penulisan, disarankan menggunakan ekstensi `.ini`.

Atribut utama dari file konfigurasi adalah sebagai berikut:

1. METHOD
2. PATH
3. PARSING_RULE
4. TRANSACTION_RULE

**METHOD**

Method adalah metode request dari klien. Method ini secara eksplisit sama antara file konfigurasi dengan request dari klien.

Contoh:

```ini
METHOD=POST
```

**PATH**

Path adalah path yang diakses oleh klien. Path ini bersifat relatif. 

Contoh:

```ini
PATH=/core/admin/add-account
```

Dalam beberapa kondisi mungkin membutuhkan path yang sama persis namun dalam kondisi yang lain hanya memerlukan kecocokan pola.

Path juga dapat berisi input dari klien. Dengan demikian, path request yang berbeda mungkin akan menjalankan proses yang sama.

Contoh:

```ini
PATH=/core/{[GROUP]}/{[TRANSACTION]}
```

**REQUEST_TYPE**

Request type adalah *content type* dari request yang dikirimkan oleh klien. *Content type* request akan menentukan bagaimaka cara simulator memparsing request dari klien. Ketidaksesuaian antara request type pada konfigurasi dengan *content type* yang dikirimkan akan menyebabkan data tidak dapat diparsing sama sekali.

*Content type* yang didukung adalah sebagai berikut:

1. `application/x-www-form-urlencoded` untuk method `GET`, `POST` dan `PUT`
2. `application/xml` untuk method `POST` dan `PUT`
3. `application/json` untuk method `POST` dan `PUT`

**RESPONSE_TYPE**

Response type adalah *content type* respon yang dikirimkan ke klien. *Content type* ini akan diinformasikan melalui header respon `Content-type`. *Content type* ini mengabaikan header request `Accept` yang dikirimkan oleh klien. Apabila pengguna ingin menggunakan nilai pada header request `Accept`, pengguna dapat membuat kondisi dengan terlebih dahulu mengambil nilai `Accept` pada parsing rule sebagai berikut:

```ini
PARSING_RULE=\
$INPUT.ACCEPT=$HEADER.ACCEPT
```

Kemudian menambahkan kondisi `$INPUT.ACCEPT` pada transaction rule.

*Content type* yang didukung lebih luas meskipun terbatas pada *content type* text misalnya sebagai berikut:

1. `text/plan`
2. `text/html`
3. `application/csv`
4. `application/xml`
5. `application/json`

**PARSING_RULE**

Parsing rule digunakan untuk memparsing request dari klien. Sumber data yang digunakan antara lain adalah sebagai berikut:

1. `$HEADER` yaitu request header dengan  nama header yang eksplisit
2. `$AUTHORIZATION_BASIC` yaitu username dan password pada basic authentication
3. `$URL` yaitu nilai yang cocok dari pola URL dengan membandingkan antara URL pada file konfigurasi dengan URL pada request dari klien
4. `$GET` yaitu nilai yang diambil dari parameter yang dikirimkan melalui URL dengan pengkodean `x-www-form-urlencode`
5. `$POST` yaitu nilai yang diambil dari body request dengan method `POST` dengan pengkodean `x-www-form-urlencode`
6. `$PUT` yaitu nilai yang diambil dari body request dengan method `PUT` dengan pengkodean `x-www-form-urlencode`
7. `$REQUEST` yaitu alternatif dari `$GET`, `$POST` dan `$PUT` tergantung dari method yang digunakan pada file konfigurasi. Berbeda dengan `$GET`, `$PUT` dan `$POST` yang hanya mendukung `x-www-form-urlencode`, `$REQUEST` mendukung *content type* `x-www-form-urlencode`, JSON dan XML baik objek maupun array.

Nilai yang diambil dari request disimpan pada variabel yang diawali dengan `$INPUT.` dan diikuti dengan nama unik dari variabel tersebut.

Untuk mengakses input yang diparsing dapat menggunakan operator titik (`.`) yang menandakan nilai diambil dari objek atau menggunakan konsep objek atau dengan menggunakan operator kurung siku (`[]`) yang menandakan nilai diambil dari array atau menggunakan konsep array.

Contoh:

```ini
PARSING_RULE=\
$INPUT.TIME_STAMP=$HEADER.X_TIME_STAMP\
$INPUT.USERNAME=$AUTHORIZATION_BASIC.USERNAME\
$INPUT.PASSWORD=$AUTHORIZATION_BASIC.PASSWORD\
$INPUT.GROUP=$URL.GROUP\
$INPUT.TRANSACTION=$URL.TRANSACTION\
$INPUT.COMMAND=$REQUEST.command\
$INPUT.DESTIONATION_ACCOUNT_NUMBER=$REQUEST.data.destination_account_number\
$INPUT.AMOUNT=$REQUEST.data.amount\
$INPUT.CURRENCY_CODE=$REQUEST.data.currency_code\
$INPUT.REFERENCE_NUMBER=$REQUEST.data.reference_number\
```

**TRANSACTION_RULE**

Transaction rule adalah pengaturan output baik header respon, body respon, HTTP status respon, delay respon, header callback, body callback, dan time out callback sesuai dengan nilai variabel dari request yang telah berhasil diparsing.

Pemilihan kondisi menggunakan kata kunci `{[IF]}` dengan catatan apabila suatu kondisi sudah terpenuhi, maka simulator akan berhenti pada kondisi tersebut dan memproses output sesuai dengan aturan yang ditentukan. Apabila dua buah kondisi atau lebih dipenuhi dalam satu request yang sama, maka kondisi yang paling atas yang akan digunakan.

Contoh:

```ini
TRANSACTION_RULE=\
{[IF]} ($INPUT.USERNAME == "user1" && $INPUT.PASSWORD == "passwd1")\
{[THEN]}\
$OUTPUT.STATUS=200 OK\
$OUTPUT.HEADER=Server: Universal REST Simulator\
X-Timestamp: $DATE('Y-m-d\\TH:i:s', 'UTC').000Z\
$OUPUT.BODY={\
    "command": "$INPUT.COMMAND",\
    "data": {\
        "amount": $INPUT.AMOUNT,\
        "currency_code": "$INPUT.CURRENCY_CODE",\
        "reference_number": "$INPUT.REFERENCE_NUMBER",\
        "time_stamp": "$DATE('Y-m-d\\TH:i:s', 'UTC').000Z"
    },\
    "response_code": "001",
    "response_text": "Success"
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.STATUS=403 Forbidden\
$OUTPUT.HEADER=Server: Universal REST Simulator\
X-Timestamp: $DATE('Y-m-d\\TH:i:s', 'UTC').000Z\
$OUPUT.BODY={\
    "command": "$INPUT.COMMAND",\
    "data": {\
        "amount": $INPUT.AMOUNT,\
        "currency_code": "$INPUT.CURRENCY_CODE",\
        "reference_number": "$INPUT.REFERENCE_NUMBER",\
        "time_stamp": "$DATE('Y-m-d\\TH:i:s', 'UTC').000Z"
    },\
    "response_code": "051",
    "response_text": "Rejected"
}\
{[ENDIF]}\
``` 

## Simulator Sederhana GET application/x-www-form-urlencoded

Contoh Konfigurasi:

```ini
METHOD=GET

PATH=/getdata

REQUEST_TYPE=application/x-www-form-urlencoded

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.NAME=$REQUEST.name\
$INPUT.EMAIL=$REQUEST.email\
$INPUT.PHONE=$REQUEST.phone

TRANSACTION_RULE=\
{[IF]} ($INPUT.NAME != "" && $INPUT.EMAIL != "")\
{[THEN]}\
$OUTPUT.STATUS=200 OK\
$OUTPUT.BODY={\
    "response_code": "001",\
    "response_text": "Success",\
    "data": {\
        "name": "$INPUT.NAME",\
        "email": "$INPUT.EMAIL",\
        "phone": "$INPUT.PHONE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.STATUS=400 Bad Request\
$OUTPUT.BODY={\
    "response_code": "061",\
    "response_text": "Mandatory field can not be empty",\
    "data": {\
        "name": "$INPUT.NAME",\
        "email": "$INPUT.EMAIL",\
        "phone": "$INPUT.PHONE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
```

Contoh Request:

```http
GET /getdata?name=Bambang&email=bambang@domain.tld&phone=08111111111 HTTP/1.1
Host: 127.0.0.1
User-Agent: Service
Accept: application/json
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Length: 216

{
    "response_code": "001",
    "response_text": "Success",
    "data": {
        "name": "Bambang",
        "email": "bambang@domain.tld",
        "phone": "08111111111",
        "time_stamp": "1619922480"
    }
}
```

## Simulator Sederhana POST application/x-www-form-urlencoded

Contoh Konfigurasi:

```ini
METHOD=POST

PATH=/postdata

REQUEST_TYPE=application/x-www-form-urlencoded

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.NAME=$REQUEST.name\
$INPUT.EMAIL=$REQUEST.email\
$INPUT.PHONE=$REQUEST.phone

TRANSACTION_RULE=\
{[IF]} ($INPUT.NAME != "" && $INPUT.EMAIL != "")\
{[THEN]}\
$OUTPUT.STATUS=200 OK\
$OUTPUT.BODY={\
    "response_code": "001",\
    "response_text": "Success",\
    "data": {\
        "name": "$INPUT.NAME",\
        "email": "$INPUT.EMAIL",\
        "phone": "$INPUT.PHONE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.STATUS=400 Bad Request\
$OUTPUT.BODY={\
    "response_code": "061",\
    "response_text": "Mandatory field can not be empty",\
    "data": {\
        "name": "$INPUT.NAME",\
        "email": "$INPUT.EMAIL",\
        "phone": "$INPUT.PHONE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
```

Contoh Request:

```http
POST /postdata HTTP/1.1
Host: 127.0.0.1
User-Agent: Service
Accept: application/json
Content-Type: application/x-www-form-urlencoded
Content-Length: 55

name=Bambang&email=bambang@domain.tld&phone=08111111111
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Length: 216

{
    "response_code": "001",
    "response_text": "Success",
    "data": {
        "name": "Bambang",
        "email": "bambang@domain.tld",
        "phone": "08111111111",
        "time_stamp": "1619922480"
    }
}
```

## Simulator Sederhana PUT application/x-www-form-urlencoded

Contoh Konfigurasi:

```ini
METHOD=PUT

PATH=/putdata

REQUEST_TYPE=application/x-www-form-urlencoded

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.NAME=$REQUEST.name\
$INPUT.EMAIL=$REQUEST.email\
$INPUT.PHONE=$REQUEST.phone

TRANSACTION_RULE=\
{[IF]} ($INPUT.NAME != "" && $INPUT.EMAIL != "")\
{[THEN]}\
$OUTPUT.STATUS=200 OK\
$OUTPUT.BODY={\
    "response_code": "001",\
    "response_text": "Success",\
    "data": {\
        "name": "$INPUT.NAME",\
        "email": "$INPUT.EMAIL",\
        "phone": "$INPUT.PHONE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.STATUS=400 Bad Request\
$OUTPUT.BODY={\
    "response_code": "061",\
    "response_text": "Mandatory field can not be empty",\
    "data": {\
        "name": "$INPUT.NAME",\
        "email": "$INPUT.EMAIL",\
        "phone": "$INPUT.PHONE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
```

Contoh Request:

```http
PUT /putdata HTTP/1.1
Host: 127.0.0.1
User-Agent: Service
Accept: application/json
Content-Type: application/x-www-form-urlencoded
Content-Length: 55

name=Bambang&email=bambang@domain.tld&phone=08111111111
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Length: 216

{
    "response_code": "001",
    "response_text": "Success",
    "data": {
        "name": "Bambang",
        "email": "bambang@domain.tld",
        "phone": "08111111111",
        "time_stamp": "1619922480"
    }
}
```

## Simulator Sederhana POST application/json

Contoh Konfigurasi:

```ini
METHOD=POST

PATH=/postjson

REQUEST_TYPE=application/json

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.NAME=$REQUEST.name\
$INPUT.EMAIL=$REQUEST.email\
$INPUT.PHONE=$REQUEST.phone

TRANSACTION_RULE=\
{[IF]} ($INPUT.NAME != "" && $INPUT.EMAIL != "")\
{[THEN]}\
$OUTPUT.STATUS=200 OK\
$OUTPUT.BODY={\
    "response_code": "001",\
    "response_text": "Success",\
    "data": {\
        "name": "$INPUT.NAME",\
        "email": "$INPUT.EMAIL",\
        "phone": "$INPUT.PHONE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.STATUS=400 Bad Request\
$OUTPUT.BODY={\
    "response_code": "061",\
    "response_text": "Mandatory field can not be empty",\
    "data": {\
        "name": "$INPUT.NAME",\
        "email": "$INPUT.EMAIL",\
        "phone": "$INPUT.PHONE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
```

Contoh Request:

```http
POST /postjson HTTP/1.1
Host: 127.0.0.1
User-Agent: Service
Accept: application/json
Content-Type: application/json
Content-Length: 89

{
    "name": "Bambang",
    "email": "bambang@domain.tld",
    "phone": "08111111111"
}
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Length: 216

{
    "response_code": "001",
    "response_text": "Success",
    "data": {
        "name": "Bambang",
        "email": "bambang@domain.tld",
        "phone": "08111111111",
        "time_stamp": "1619922480"
    }
}
```

## Simulator Sederhana PUT application/json

Contoh Konfigurasi:

```ini
METHOD=PUT

PATH=/putjson

REQUEST_TYPE=application/json

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.NAME=$REQUEST.name\
$INPUT.EMAIL=$REQUEST.email\
$INPUT.PHONE=$REQUEST.phone

TRANSACTION_RULE=\
{[IF]} ($INPUT.NAME != "" && $INPUT.EMAIL != "")\
{[THEN]}\
$OUTPUT.STATUS=200 OK\
$OUTPUT.BODY={\
    "response_code": "001",\
    "response_text": "Success",\
    "data": {\
        "name": "$INPUT.NAME",\
        "email": "$INPUT.EMAIL",\
        "phone": "$INPUT.PHONE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.STATUS=400 Bad Request\
$OUTPUT.BODY={\
    "response_code": "061",\
    "response_text": "Mandatory field can not be empty",\
    "data": {\
        "name": "$INPUT.NAME",\
        "email": "$INPUT.EMAIL",\
        "phone": "$INPUT.PHONE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
```

Contoh Request:

```http
PUT /putjson HTTP/1.1
Host: 127.0.0.1
User-Agent: Service
Accept: application/json
Content-Type: application/json
Content-Length: 89

{
    "name": "Bambang",
    "email": "bambang@domain.tld",
    "phone": "08111111111"
}
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Length: 216

{
    "response_code": "001",
    "response_text": "Success",
    "data": {
        "name": "Bambang",
        "email": "bambang@domain.tld",
        "phone": "08111111111",
        "time_stamp": "1619922480"
    }
}
```

## Simulator Sederhana POST application/xml

Contoh Konfigurasi:

```ini
METHOD=POST

PATH=/postxml

REQUEST_TYPE=application/xml

RESPONSE_TYPE=application/xml

PARSING_RULE=\
$INPUT.NAME=$REQUEST.name\
$INPUT.EMAIL=$REQUEST.email\
$INPUT.PHONE=$REQUEST.phone

TRANSACTION_RULE=\
{[IF]} ($INPUT.NAME != "" && $INPUT.EMAIL != "")\
{[THEN]}\
$OUTPUT.STATUS=200 OK\
$OUTPUT.BODY=<?xml version="1.0" encoding="UTF-8"?>\
<container>\
    <response_code>001</response_code>\
    <response_text>Success</response_text>\
    <data>\
        <name>$INPUT.NAME</name>\
        <email>$INPUT.EMAIL</email>\
        <phone>$INPUT.PHONE</phone>\
        <time_stamp>$DATE('U')</time_stamp>\
    </data>\
</container>\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.STATUS=400 Bad Request\
$OUTPUT.BODY=<?xml version="1.0" encoding="UTF-8"?>\
<container>\
    <response_code>061</response_code>\
    <response_text>Mandatory field can not be empty</response_text>\
    <data>\
        <name>$INPUT.NAME</name>\
        <email>$INPUT.EMAIL</email>\
        <phone>$INPUT.PHONE</phone>\
        <time_stamp>$DATE('U')</time_stamp>\
    </data>\
</container>\
{[ENDIF]}\
```


Contoh Request:

```http
POST /postxml HTTP/1.1
Host: 127.0.0.1
User-Agent: Service
Accept: application/xml
Content-Type: application/xml
Content-Length: 157

<?xml version="1.0" encoding="UTF-8"?>
<container>
    <name>Bambang</name>
    <email>bambang@domain.tld</email>
    <phone>08111111111</phone>
</container>
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Length: 319

<?xml version="1.0" encoding="UTF-8"?>
<container>
    <response_code>001</response_code>
    <response_text>Success</response_text>
    <data>\
        <name>Bambang</name>
        <email>bambang@domain.tld</email>
        <phone>08111111111</phone>
        <time_stamp>1619922480</time_stamp>
    </data>
</container>
```

## Simulator Sederhana PUT application/xml

Contoh Konfigurasi:

```ini
METHOD=PUT

PATH=/putxml

REQUEST_TYPE=application/xml

RESPONSE_TYPE=application/xml

PARSING_RULE=\
$INPUT.NAME=$REQUEST.name\
$INPUT.EMAIL=$REQUEST.email\
$INPUT.PHONE=$REQUEST.phone

TRANSACTION_RULE=\
{[IF]} ($INPUT.NAME != "" && $INPUT.EMAIL != "")\
{[THEN]}\
$OUTPUT.STATUS=200 OK\
$OUTPUT.BODY=<?xml version="1.0" encoding="UTF-8"?>\
<container>\
    <response_code>001</response_code>\
    <response_text>Success</response_text>\
    <data>\
        <name>$INPUT.NAME</name>\
        <email>$INPUT.EMAIL</email>\
        <phone>$INPUT.PHONE</phone>\
        <time_stamp>$DATE('U')</time_stamp>\
    </data>\
</container>\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.STATUS=400 Bad Request\
$OUTPUT.BODY=<?xml version="1.0" encoding="UTF-8"?>\
<container>\
    <response_code>061</response_code>\
    <response_text>Mandatory field can not be empty</response_text>\
    <data>\
        <name>$INPUT.NAME</name>\
        <email>$INPUT.EMAIL</email>\
        <phone>$INPUT.PHONE</phone>\
        <time_stamp>$DATE('U')</time_stamp>\
    </data>\
</container>\
{[ENDIF]}\
```

Contoh Request:

```http
PUT /putxml HTTP/1.1
Host: 127.0.0.1
User-Agent: Service
Accept: application/xml
Content-Type: application/xml
Content-Length: 157

<?xml version="1.0" encoding="UTF-8"?>
<container>
    <name>Bambang</name>
    <email>bambang@domain.tld</email>
    <phone>08111111111</phone>
</container>
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/xml
Content-Length: 319

<?xml version="1.0" encoding="UTF-8"?>
<container>
    <response_code>001</response_code>
    <response_text>Success</response_text>
    <data>\
        <name>Bambang</name>
        <email>bambang@domain.tld</email>
        <phone>08111111111</phone>
        <time_stamp>1619922480</time_stamp>
    </data>
</container>
```

## Simulator Sederhana POST application/soap+xml

Contoh Konfigurasi:

```ini
METHOD=POST

PATH=/postsoap

REQUEST_TYPE=applicatiom/soap+xml

RESPONSE_TYPE=applicatiom/soap+xml

PARSING_RULE=\
$INPUT.MSISDN=$REQUEST.Body.YtzTopupRequest.msisdn\
$INPUT.PRODUCT_CODE=$REQUEST.Body.YtzTopupRequest.productCode\
$INPUT.REFERENCE_NUMBER=$REQUEST.Body.YtzTopupRequest.clientRefID

TRANSACTION_RULE=\
{[IF]} ($INPUT.MSISDN == "081266666667")\
{[THEN]}\
$OUTPUT.CALLBACK_URL=http://localhost:8080/process-callback\
$OUTPUT.CALLBACK_METHOD=POST\
$OUTPUT.CALLBACK_TYPE=applicatiom/soap+xml\
$OUTPUT.CALLBACK_BODY=<?xml version="1.0" encoding="utf-8"?>\
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">\
  <soap:Body>\
    <YtzTopupRequest xmlns="http://ytz.org/">\
      <ResponseCode>0</ResponseCode>\
      <TransID>164</TransID>\
      <ReferenceID>$INPUT.REFERENCE_NUMBER</ReferenceID>\
      <SerialNo>0987654321</SerialNo>\
    </YtzTopupRequest>\
  </soap:Body>\
</soap:Envelope>\
$OUTPUT.BODY=<?xml version="1.0" encoding="utf-8"?>\
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">\
  <soap:Body>\
    <YtzTopupRequest xmlns="http://ytz.org/">\
      <ResponseCode>1</ResponseCode>\
      <TransID>164</TransID>\
      <ReferenceID>$INPUT.REFERENCE_NUMBER</ReferenceID>\
      <SerialNo>0987654321</SerialNo>\
    </YtzTopupRequest>\
  </soap:Body>\
</soap:Envelope>\
{[ENDIF]}\
{[IF]} ($INPUT.MSISDN == "081266666666")\
{[THEN]}\
$OUTPUT.CALLBACK_URL=http://localhost:8080/process-callback\
$OUTPUT.CALLBACK_METHOD=POST\
$OUTPUT.CALLBACK_TYPE=applicatiom/soap+xml\
$OUTPUT.CALLBACK_BODY=<?xml version="1.0" encoding="utf-8"?>\
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">\
  <soap:Body>\
    <YtzTopupRequest xmlns="http://ytz.org/">\
      <ResponseCode>0</ResponseCode>\
      <TransID>164</TransID>\
      <ReferenceID>$INPUT.REFERENCE_NUMBER</ReferenceID>\
      <SerialNo>0987654321</SerialNo>\
    </YtzTopupRequest>\
  </soap:Body>\
</soap:Envelope>\
$OUTPUT.BODY=<?xml version="1.0" encoding="utf-8"?>\
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">\
  <soap:Body>\
    <YtzTopupRequest xmlns="http://ytz.org/">\
      <ResponseCode>0</ResponseCode>\
      <TransID>164</TransID>\
      <ReferenceID>$INPUT.REFERENCE_NUMBER</ReferenceID>\
      <SerialNo>0987654321</SerialNo>\
    </YtzTopupRequest>\
  </soap:Body>\
</soap:Envelope>\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.BODY=<?xml version="1.0" encoding="utf-8"?>\
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">\
  <soap:Body>\
    <YtzTopupRequest xmlns="http://ytz.org/">\
      <ResponseCode>17</ResponseCode>\
      <TransID>164</TransID>\
      <ReferenceID>$INPUT.REFERENCE_NUMBER</ReferenceID>\
      <SerialNo>0987654321</SerialNo>\
    </YtzTopupRequest>\
  </soap:Body>\
</soap:Envelope>\
{[ENDIF]}\
```

Pada `Content-type: application/soap+xml`, prefix `soap:` pada setiap tag dihapus sehingga pengguna dapat mengambil input dari request tanpa prefix `soap:`. Sebagai contoh: data di dalam tag `<soap:Body>` dapat diambil dengan `$REQUEST.Body` atau `$REQUEST[Body]` bukan `$REQUEST[soap:Body]`.

Contoh Request:

```http
PUT /postsoap HTTP/1.1
Host: 127.0.0.1
User-Agent: Service
Accept: application/soap+xml
Content-Type: application/soap+xml
Content-Length: 543

<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <YtzTopupRequest xmlns="http://ytz.org/">
      <msisdn>081266666666</msisdn>
      <productCode>X010</productCode>
      <userID>USER</userID>
      <userPassword>PASS</userPassword>
      <clientRefID>0000000000013787</clientRefID>
      <storeid>XL</storeid>
    </YtzTopupRequest>
  </soap:Body>
</soap:Envelope>
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/soap+xml
Content-Length: 477

<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <YtzTopupRequest xmlns="http://ytz.org/">
      <ResponseCode>0</ResponseCode>
      <TransID>164</TransID>
      <ReferenceID>0000000000013787</ReferenceID>
      <SerialNo>0987654321</SerialNo>
    </YtzTopupRequest>
  </soap:Body>
</soap:Envelope>
```

## Kombinasi GET dan POST

Universal REST Simulator dapat mengkombinasikan input `GET` dengan `POST`. Untuk mengkombinasikan `GET` dengan `POST`, gunakan method `POST`. 

`POST` hanya berlaku untuk `REQUEST_TYPE=application/x-www-form-urlencoded` . Dalam hal ini, client juga harus mengirim `Content-type: application/x-www-form-urlencoded`. Pengambilan input dari `GET` dan `POST` sama dengan `REQUEST` seperti contoh sebagai berikut:

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
    "email": "token@doconfig1n.tld"\
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

 Contoh Request:

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

Pengguna dapat membuat konfigurasi yang memungkinkan klien mengirim data dengan tipe `application/json` dan mengkombinasikannya dengan `GET`. Dalam hal ini, `REQUEST_TYPE` diset menjadi `application/json`. Tentu saja data yang dikirim dengan `GET` menggunakan `application/x-www-form-urlencoded`.

Contoh Konfigurasi:

```ini
PATH=/universal-simulator/getandpost

METHOD=POST

REQUEST_TYPE=application/json

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.ACTION=$GET.action\
$INPUT.ACCOUNT_NUMBER=$REQUEST.data.account_number\
$INPUT.AMOUNT=$REQUEST.data.amount\
$INPUT.CURRENCY_CODE=$REQUEST.data.currency_code

TRANSACTION_RULE=\
{[IF]} ($INPUT.ACTION == "cash-deposit" && $INPUT.ACCOUNT_NUMBER != "" && $INPUT.AMOUNT > 0)\
{[THEN]}\
$OUTPUT.STATUS=200\
$OUTPUT.BODY={\
    "response_code": "001",\
    "response_text:" "Success",\
    "data": {\
        "account_number": "$INPUT.ACCOUNT_NUMBER",\
        "amount": $INPUT.AMOUNT,\
        "currency_code": "$INPUT.CURRENCY_CODE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.STATUS=400\
$OUTPUT.BODY={\
    "response_code": "061",\
    "response_text:" "Mandatory field can not be empty",\
    "data": {\
        "account_number": "$INPUT.ACCOUNT_NUMBER",\
        "amount": $INPUT.AMOUNT,\
        "currency_code": "$INPUT.CURRENCY_CODE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
```

Contoh Request:

```http
POST /universal-simulator/getandpost?action=cash-deposit HTTP/1.1 
Host: 127.0.0.1
Content-type: application/json
Content-length: 121

{
    "data": {
        "account_number": "1234567890",
        "amount": 5000000,
        "currency_code": "IDR"
    }
}
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Length: 218

{
    "response_code": "001",
    "response_text:" "Success",
    "data": {
        "account_number": "1234567890",
        "amount": 5000000,
        "currency_code": "IDR",
        "time_stamp": "1619922480"
    }\
}
```

## Kombinasi GET dan PUT

Universal REST Simulator dapat mengkombinasikan input `GET` dengan `PUT`. Untuk mengkombinasikan `GET` dengan `PUT`, gunakan method `PUT`. 

`PUT` hanya berlaku untuk `REQUEST_TYPE=application/x-www-form-urlencoded` . Dalam hal ini, client juga harus mengirim `Content-type: application/x-www-form-urlencoded`. Pengambilan input dari `GET` dan `PUT` sama dengan `REQUEST` seperti contoh sebagai berikut:

```ini
PATH=/universal-simulator/token

METHOD=PUT

REQUEST_TYPE=application/x-www-form-urlencoded

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.USERNAME=$AUTHORIZATION_BASIC.USERNAME\
$INPUT.PASSWORD=$AUTHORIZATION_BASIC.PASSWORD\
$INPUT.GRANT_TYPE=$PUT.grant_type\
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
    "email": "token@doconfig1n.tld"\
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
Konfigurasi di atas menunjukkan bahwa path tersebut menghendaki method `PUT` dan yang lain. Akan tetapi, pengguna tetap dapat mengambil nilai dari query pada `URL` menggunakan `$GET`.

Contoh Request:

```http
PUT /universal-simulator/token?detail=yes HTTP/1.1 
Host: 127.0.0.1
Authorization: Basic dXNlcm5hbWU6cGFzc3dvcmQ=
Content-type: application/x-www-form-urlencoded
Content-length: 29

grant_type=client_credentials
```

Dari contoh di atas, input dari URL `/universal-simulator/token?detail=yes` diambil dengan `$GET.detail`. Nilai ini akan sama dengan `$REQUEST.detail` jika menggunakan method `GET`. Karena pada konfigurasi telah didefinisikan`METHOD=PUT`, maka nilai ini hanya bisa diambil dengan `$GET.detail` karena `$REQUEST` hanya mengacu kepada request body yang dikirim.

Pengambilan data dari body dapat dilakukan dengan dua cara yaitu `$REQUEST` dan `$PUT`. Ingat bahwa `$PUT` hanya bisa digunakan jika `REQUEST_TYPE=application/x-www-form-urlencoded` dan `Content-type: application/x-www-form-urlencoded`. Untuk content type lain, harus menggunakan `$RQUEST`.

Pengguna dapat membuat konfigurasi yang memungkinkan klien mengirim data dengan tipe `application/json` dan mengkombinasikannya dengan `GET`. Dalam hal ini, `REQUEST_TYPE` diset menjadi `application/json`. Tentu saja data yang dikirim dengan `GET` menggunakan `application/x-www-form-urlencoded`.

Contoh Konfigurasi:

```ini
PATH=/universal-simulator/getandput

METHOD=PUT

REQUEST_TYPE=application/json

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.ACTION=$GET.action\
$INPUT.ACCOUNT_NUMBER=$REQUEST.data.account_number\
$INPUT.AMOUNT=$REQUEST.data.amount\
$INPUT.CURRENCY_CODE=$REQUEST.data.currency_code

TRANSACTION_RULE=\
{[IF]} ($INPUT.ACTION == "cash-deposit" && $INPUT.ACCOUNT_NUMBER != "" && $INPUT.AMOUNT > 0)\
{[THEN]}\
$OUTPUT.STATUS=200\
$OUTPUT.BODY={\
    "response_code": "001",\
    "response_text:" "Success",\
    "data": {\
        "account_number": "$INPUT.ACCOUNT_NUMBER",\
        "amount": $INPUT.AMOUNT,\
        "currency_code": "$INPUT.CURRENCY_CODE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.STATUS=400\
$OUTPUT.BODY={\
    "response_code": "061",\
    "response_text:" "Mandatory field can not be empty",\
    "data": {\
        "account_number": "$INPUT.ACCOUNT_NUMBER",\
        "amount": $INPUT.AMOUNT,\
        "currency_code": "$INPUT.CURRENCY_CODE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
```

Contoh Request:

```http
PUT /universal-simulator/getandput?action=cash-deposit HTTP/1.1 
Host: 127.0.0.1
Content-type: application/json
Content-length: 121

{
    "data": {
        "account_number": "1234567890",
        "amount": 5000000,
        "currency_code": "IDR"
    }
}
```

## Simulator Sederhana Input dari URL

Tidak jarang developer menggunakan path pada URL sebagai cara untuk mengambil nilai dari klien. Tentu saja aplikasi harus dapat mengekstrak informasi tersebut dari path request yang diberikan. 

Nilai yang diambil dari path pun kadang lebih dari satu yang dipisahkan dengan tanda baca garis miring (`/`). Akan tetapi, ada pula developer yang menggunakan pola tidak umum pada path aplikasi.

Contoh Konfigurasi:

```ini
METHOD=GET

PATH=/geturldata/{[TRANSACTION]}/{[ID]}

REQUEST_TYPE=application/x-www-form-urlencode

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.TRANSACTION_TYPE=$URL.TRANSACTION\
$INPUT.TRANSACTION_ID=$URL.ID

TRANSACTION_RULE=\
{[IF]} ($INPUT.TRANSACTION_TYPE == "detail" && $INPUT.TRANSACTION_ID != "")\
{[THEN]}
$OUTPUT.STATUS=200\
$OUTPUT.BODY={\
    "action": "$INPUT.TRANSACTION_TYPE",\
    "id": "$INPUT.TRANSACTION_ID",\
    "data": {\
        "name": "Bambang",\
        "account_number": "123456",\
        "amount": 5000000\
    },\
    "response_code": "001",\
    "response_text": "Success"\
}\
{[ENDIF]}
{[IF]} (true)\
{[THEN]}
$OUTPUT.STATUS=400\
$OUTPUT.BODY={\
    "action": "$INPUT.TRANSACTION_TYPE",\
    "id": "$INPUT.TRANSACTION_ID",\
    "data": {},\
    "response_code": "061",\
    "response_text": "Mandatory field can not be empty"\
}\
{[ENDIF]}
```

Contoh Request:

```http
GET /geturldata/detail/69 HTTP/1.1 
Host: 127.0.0.1
User-Agent: Service
Accept: application/json
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Lengh: 212

{
    "action": "detail",
    "id": "69",
    "data": {
        "name": "Bambang",
        "account_number": "123456",
        "amount": 5000000
    },
    "response_code": "001",
    "response_text": "Success"
}
```

Penggunaan path tidak hanya untuk method `GET` saja namun bisa juga digunakan pada method `POST` dan `PUT`.

Contoh Konfigurasi:

```ini
METHOD=POST

PATH=/posturldata/{[TRANSACTION]}/{[ID]}

REQUEST_TYPE=application/json

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.TRANSACTION_TYPE=$URL.TRANSACTION\
$INPUT.ACCOUNT_NUMBER=$REQUEST.data.account_number\
$INPUT.AMOUNT=$REQUEST.data.amount\
$INPUT.CURRENCY=$REQUEST.data.currency_code

TRANSACTION_RULE=\
{[IF]} ($INPUT.TRANSACTION_TYPE == "deposit" && $INPUT.ACCOUNT_NUMBER != "" && $INPUT.AMOUNT > 0)\
{[THEN]}
$OUTPUT.STATUS=200\
$OUTPUT.BODY={\
    "action": "$INPUT.TRANSACTION_TYPE",\
    "id": 69,\
    "data": {\
        "name": "Bambang",\
        "account_number": "$INPUT.ACCOUNT_NUMBER",\
        "amount": $INPUT.AMOUNT,\
        "currency_code": "$INPUT.CURRENCY"\
    },\
    "response_code": "001",\
    "response_text": "Success"\
}\
{[ENDIF]}
{[IF]} (true)\
{[THEN]}
$OUTPUT.STATUS=400\
$OUTPUT.BODY={\
    "action": "$INPUT.TRANSACTION_TYPE",\
    "id": "$INPUT.TRANSACTION_ID",\
    "data": {},\
    "response_code": "061",\
    "response_text": "Mandatory field can not be empty"\
}\
{[ENDIF]}
```

Contoh Request:

```http
GET /posturldata/deposit HTTP/1.1 
Host: 127.0.0.1
User-Agent: Service
Content-Type: application/json
Accept: application/json
Content-Length: 118

{
    "data": {
        "account_number": "98765432",
        "amount": 250000,
        "currency_code": "IDR"
    }
}
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Lengh: 242

{
    "action": "deposit",
    "id": 69,
    "data": {
        "name": "Bambang",
        "account_number": "123456",
        "amount": 250000,
        "currency_code": "IDR"
    },
    "response_code": "001",
    "response_text": "Success"
}
```

## Simulator Sederhana Input dari Header

Contoh Konfigurasi:

```ini
METHOD=POST

PATH=/fromheader

REQUEST_TYPE=application/json

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.DATE_TIME=$HEADER.X_DATE_TIME\
$INPUT.SIGNATURE=$HEADER.X_SIGNATURE\
$INPUT.API_KEY=$HEADER.X_API_KEY\
$INPUT.ACCOUNT_NUMBER=$REQUEST.data.account_number\
$INPUT.AMOUNT=$REQUEST.data.amount\
$INPUT.CURRENCY=$REQUEST.data.currency_code

TRANSACTION_RULE=\
{[IF]} ($INPUT.API_KEY == "api123" && $INPUT.DATE_TIME != "" && $INPUT.SIGNATURE != "" && $INPUT.ACCOUNT_NUMBER != "" && $INPUT.AMOUNT > 0)\
{[THEN]}
$OUTPUT.STATUS=200\
$OUTPUT.BODY={\
    "action": "$INPUT.TRANSACTION_TYPE",\
    "id": 69,\
    "data": {\
        "date_time": "$INPUT.DATE_TIME",\
        "name": "Bambang",\
        "account_number": "$INPUT.ACCOUNT_NUMBER",\
        "amount": $INPUT.AMOUNT,\
        "currency_code": "$INPUT.CURRENCY"\
    },\
    "response_code": "001",\
    "response_text": "Success"\
}\
{[ENDIF]}
{[IF]} (true)\
{[THEN]}
$OUTPUT.STATUS=400\
$OUTPUT.BODY={\
    "action": "$INPUT.TRANSACTION_TYPE",\
    "id": "$INPUT.TRANSACTION_ID",\
    "data": {},\
    "response_code": "061",\
    "response_text": "Mandatory field can not be empty"\
}\
{[ENDIF]}
```

Contoh Request:

```http
GET /fromheader HTTP/1.1 
Host: 127.0.0.1
User-Agent: Service
Content-Type: application/json
Accept: application/json
Content-Length: 118
X-Api-Key: api123
X-Date-Time: 2021-05-01T10:11:12.000Z
X-Signature: yuYTDtrdoiioidtydDRryooTtee588iKJesrrfr1

{
    "data": {
        "account_number": "98765432",
        "amount": 250000,
        "currency_code": "IDR"
    }
}
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Lengh: 242

{
    "action": "deposit",
    "id": 69,
    "data": {
        "date_time": "2021-05-01T10:11:12.000Z",
        "name": "Bambang",
        "account_number": "123456",
        "amount": 250000,
        "currency_code": "IDR"
    },
    "response_code": "001",
    "response_text": "Success"
}
```

## Simulator Sederhana Input dari Basic Authorization

Pengguna dapat mengambil username dan password dari Basic Authorization tanpa harus memparsingnya secara manual. Simulator secara otomatis memparsing Basic Authorization jika konfigurasi menghendakinya.

Contoh Konfigurasi:

```ini
METHOD=POST

PATH=/basicauth

REQUEST_TYPE=application/json

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.USERNAME=$AUTHORIZATION_BASIC.USERNAME\
$INPUT.PASSWORD=$AUTHORIZATION_BASIC.PASSWORD\
$INPUT.ACCOUNT_NUMBER=$REQUEST.data.account_number\
$INPUT.AMOUNT=$REQUEST.data.amount\
$INPUT.CURRENCY=$REQUEST.data.currency_code

TRANSACTION_RULE=\
{[IF]} ($INPUT.USERNAME == "user1" && $INPUT.PASSWORD == "password1" && $INPUT.ACCOUNT_NUMBER != "" && $INPUT.AMOUNT > 0)\
{[THEN]}
$OUTPUT.STATUS=200\
$OUTPUT.BODY={\
    "action": "$INPUT.TRANSACTION_TYPE",\
    "id": 69,\
    "data": {\
        "date_time": "$INPUT.DATE_TIME",\
        "name": "Bambang",\
        "account_number": "$INPUT.ACCOUNT_NUMBER",\
        "amount": $INPUT.AMOUNT,\
        "currency_code": "$INPUT.CURRENCY"\
    },\
    "response_code": "001",\
    "response_text": "Success"\
}\
{[ENDIF]}
{[IF]} (true)\
{[THEN]}
$OUTPUT.STATUS=403\
$OUTPUT.BODY={\
    "action": "$INPUT.TRANSACTION_TYPE",\
    "id": "$INPUT.TRANSACTION_ID",\
    "data": {},\
    "response_code": "062",\
    "response_text": "Access forbidden"\
}\
{[ENDIF]}
```

Contoh Request:

```http
GET /basicauth HTTP/1.1 
Host: 127.0.0.1
User-Agent: Service
Content-Type: application/json
Accept: application/json
Content-Length: 118
Authorization: Basic dXNlcjE6cGFzc3dvcmQx

{
    "data": {
        "account_number": "98765432",
        "amount": 250000,
        "currency_code": "IDR"
    }
}
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Lengh: 291

{
    "action": "deposit",
    "id": 69,
    "data": {
        "date_time": "2021-05-01T10:11:12.000Z",
        "name": "Bambang",
        "account_number": "123456",
        "amount": 250000,
        "currency_code": "IDR"
    },
    "response_code": "001",
    "response_text": "Success"
}
```

## Simulator Sederhana Output UUID

Pada beberapa kasus, pengguna mungkin membutuhkan respon dengan ID yang dinamik dan unik. Universal REST Simulator memungkinkan pengguna untuk menghasilkan UUID. 

Pengguna dapat menggunakan banyak UUID pada sebuah file konfigurasi.

Contoh Konfigurasi:

```ini
PATH=/universal-simulator/token

METHOD=POST

REQUEST_TYPE=application/x-www-form-urlencoded

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.USERNAME=$AUTHORIZATION_BASIC.USERNAME\
$INPUT.PASSWORD=$AUTHORIZATION_BASIC.PASSWORD\
$INPUT.UUID1=$SYSTEM.UUID\
$INPUT.UUID2=$SYSTEM.UUID\
$INPUT.UUID3=$SYSTEM.UUID\
$INPUT.UUID4=$SYSTEM.UUID

TRANSACTION_RULE=\
{[IF]} ($INPUT.USERNAME == "user1" && $INPUT.PASSWORD == "password1")\
{[THEN]} $OUTPUT.DELAY=0\
$OUTPUT.DELAY=0\
$OUTPUT.STATUS=200\
$OUTPUT.BODY={\
    "respnse_code":"001",\
    "data":{\
        "unique_id_1": "$INPUT.UUID1",\
        "unique_id_2": "$INPUT.UUID2",\
        "unique_id_3": "$INPUT.UUID3",\
        "unique_id_4": "$INPUT.UUID4"\
    }\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]} $OUTPUT.DELAY=0\
$OUTPUT.DELAY=0\
$OUTPUT.STATUS=403\
$OUTPUT.BODY={\
    "respnse_code":"061",
    "data":{\
    }\
}\
{[ENDIF]}\
```

Contoh Request:

```http
POST /uuid HTTP/1.1 
Host: 127.0.0.1
Authorization: Basic dXNlcjE6cGFzc3dvcmQx
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Lengh: 207

{
    "respnse_code":"001",
    "data":{
        "unique_id_1": "4b3403665fea6",
        "unique_id_2": "4b3403665eea6",
        "unique_id_3": "4b3403665dea6",
        "unique_id_4": "4b3403665cea6"
    }
}
```

## Simulator Sederhana Input Array

Pada kenyataannya, request tidak hanya mengandung objek saja melainkan juga array. Array bisa berasal dari parameter pada `GET`, `POST` dan `PUT` pada *content type* `application/x-www-form-urlencode`, `application/json` maupun `application/xml`.

Data tidak dapat diparsing dengan menggunakan operator titik (`.`). Sebagai gantinya, operator kurung siku (`[]`) dapat digunakan untuk memparsing data tersebut.

Sebagai contoh, parameter sebuah request adalah sebagai berikut:

`item[]=Kemeja&item[]=Sepatu&item[]=Topi`

Maka, untuk mengambil data di atas, dapat dilakukan dengan cara sebagai berikut:

```ini
PARSING_RULE=\
$INPUT.ITEM0=$REQUEST[item][0]\
$INPUT.ITEM1=$REQUEST[item][1]\
$INPUT.ITEM2=$REQUEST[item][2]
```

Pada bererapa web server mungkin `item[]=Kemeja&item[]=Sepatu&item[]=Topi` tidak diterima. Sebagai gantinya, klien harus menuliskan indeks secara eksplisit menjadi `item[0]=Kemeja&item[1]=Sepatu&item[2]=Topi`.

Universal REST Simulator secara cerdas dapat dengan mengambil nilai baik dengan penulisan indeks secara eksplisit maupun tidak. Selain itu, penulisan seperti `item[]=Kemeja&item[1]=Sepatu&item[3]=Topi` juga dapat diterima meskipun penulisan seperti ini sangat tidak disarankan.

Penggunaan operator kurung siku (`[]`) menganggap request sebagai sebuah *associated array* yaitu map dengan key sebuah string dan mempunyai value dengan tipe mixed meskipun dalam hal ini dibatasi pada tipe data string, numeric dan boolean.

Tidak diperbolehkan menggunakan operator  kurung siku (`[]`) dan titik (`.`) untuk mengambil sebuah nilai. Perlu diingat bahwa Universal REST Simulator akan langsung mengkonversi semua request menjadi *object* apabila inputnya diawali dengan `$REQUEST.`, `$GET.`, `$POST.`, dan `$PUT.` dan akan langsung mengkonversi semua request menjadi *associated array* apabila inputnya diawali dengan `$REQUEST[`, `$GET[`, `$POST[`, dan `$PUT[`. Oleh sebab itu, meskpun data yang akan diambil merupakan *object*, namun tetap dapat diparsing menggunakan operator kurung siku (`[]`).

Sebagai contoh, parameter request `name=Kemeja&quantity=1&price=450000` dapat diparsing dengan konfigurasi sebagai berikut:

```ini
PARSING_RULE=\
$INPUT.NAME=$REQUEST[name]\
$INPUT.QUANTITY=$REQUEST[quantity]\
$INPUT.PRICE=$REQUEST[price]
```

atau 

```ini
PARSING_RULE=\
$INPUT.NAME=$REQUEST.name\
$INPUT.QUANTITY=$REQUEST.quantity\
$INPUT.PRICE=$REQUEST.price
```

atau 

```ini
PARSING_RULE=\
$INPUT.NAME=$REQUEST.name\
$INPUT.QUANTITY=$REQUEST[quantity]\
$INPUT.PRICE=$REQUEST.price
```

## Simulator Sederhana Input Object dan Array

Pengguna mungkin menggunakan kombinasi antara `array` dan `object` sebagai `payload` dari `request` baik `GET`, `POST`, `PUT`, maupun `REQUEST`. Untuk mengambil nilai dari input yang kesemuanya adalah `object` dapat menggunakan operator titik (.) sedangkan untuk mengambil nilai dari input yang merupakan kombinasi antara `object` dan `array` dapat menggunakan operator kurung siku `[]`.

Contoh Konfigurasi:

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
{[ENDIF]}
```

Operator `[]` dapat digunakan untuk mengambil nilai dari `object` dan `array`. Tidak diperkenankan menggabungkan operator `.` dan `[]` dalam mengambil sebuah nilai. Dengan demikian, penulisan `$INPUT.AMOUNT0=$REQUEST[items][0].amount` tidak diperbolehkan. Meskipun demikian, diperbolehkan menggunakan kombinasinya pada input yang berbeda.

Contoh Kombinasi Operator:

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
{[ENDIF]}
```

Contoh Request:

```http
POST /objectandarray HTTP/1.1 
Host: 127.0.0.1
Content

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

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: text/plain
Content-Length: 113

NAMA PELANGGAN : Anonim
NAMA ITEM      : Kopi
HARGA ITEM     : 15000
NAMA ITEM      : Roti
HARGA ITEM     : 80000
```

`$INPUT.CUSTOMER_NAME=$REQUEST.customer.name` dapat pula ditulis dengan `$INPUT.CUSTOMER_NAME=$REQUEST[customer][name]` tanpa spasi sebelum `[` dan sesudah `]`.

## HTTP Status Standard

Contoh Konfigurasi:

```ini
PATH=/universal-simulator/token

METHOD=POST

REQUEST_TYPE=application/x-www-form-urlencoded

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.USERNAME=$AUTHORIZATION_BASIC.USERNAME\
$INPUT.PASSWORD=$AUTHORIZATION_BASIC.PASSWORD\
$INPUT.GRANT_TYPE=$POST.grant_type\
$INPUT.DETAIL=$GET.detail

TRANSACTION_RULE=\
{[IF]} ($INPUT.GRANT_TYPE == 'client_credentials' && $INPUT.USERNAME == "username" && $INPUT.PASSWORD == "password")\
{[THEN]} $OUTPUT.DELAY=0\
$OUTPUT.STATUS=200\
$OUTPUT.DELAY=0\
$OUTPUT.BODY={\
    "token_type": "Bearer",\
    "access_token": "$TOKEN.JWT",\
    "expire_at": $TOKEN.EXPIRE_AT,\
    "expires_in": $TOKEN.EXPIRE_IN,\
    "email": "token@doconfig1n.tld"\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]} $OUTPUT.DELAY=0\
$OUTPUT.DELAY=0\
$OUTPUT.STATUS=403\
$OUTPUT.BODY={\
}\
{[ENDIF]}\
```

Contoh Request:

```http
POST /universal-simulator/token?detail=yes HTTP/1.1 
Host: 127.0.0.1
Authorization: Basic dXNlcm5hbWU6cGFzc3dvcmQ=
Content-type: application/x-www-form-urlencoded
Content-length: 29

grant_type=client_credentials
```

Contoh Respon

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Length: 363

{
    "token_type": "Bearer",
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJZb3VyIENvbXBhbnkiLCJhdWQiOiJZb3VyIENsaWVudCIsImlhdCI6MTYyMDAyNDI0MiwibmJmIjoxNjIwMDI0MjQyLCJleHAiOjE2MjAwMjc4NDIsImRhdGEiOltdfQ.x_SOclw1fn4irxsNHtX6ai02CTnFAGB5X7O_pRtk5UA",
    "expire_at": 1620027842,
    "expires_in": 3600,
    "email": "token@doconfig1n.tld"
}
```

## HTTP Status Non-Standard

Contoh Konfigurasi:

```ini
PATH=/universal-simulator/token

METHOD=POST

REQUEST_TYPE=application/x-www-form-urlencoded

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.USERNAME=$AUTHORIZATION_BASIC.USERNAME\
$INPUT.PASSWORD=$AUTHORIZATION_BASIC.PASSWORD\
$INPUT.GRANT_TYPE=$POST.grant_type\
$INPUT.DETAIL=$GET.detail

TRANSACTION_RULE=\
{[IF]} ($INPUT.GRANT_TYPE == 'client_credentials' && $INPUT.USERNAME == "username" && $INPUT.PASSWORD == "password")\
{[THEN]} $OUTPUT.DELAY=0\
$OUTPUT.STATUS=200\
$OUTPUT.DELAY=0\
$OUTPUT.BODY={\
    "token_type": "Bearer",\
    "access_token": "$TOKEN.JWT",\
    "expire_at": $TOKEN.EXPIRE_AT,\
    "expires_in": $TOKEN.EXPIRE_IN,\
    "email": "token@doconfig1n.tld"\
}\
{[ENDIF]}\
{[IF]} ($INPUT.GRANT_TYPE == 'client_credentials' && $INPUT.USERNAME != "" && $INPUT.PASSWORD == "")\
{[THEN]} $OUTPUT.DELAY=0\
$OUTPUT.DELAY=0\
$OUTPUT.STATUS=403\
$OUTPUT.BODY={\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.STATUS=999 Invalid Request\
$OUTPUT.BODY={\
}\
{[ENDIF]}\
```

Pada HTTP Status Non-Standard (*unofficial HTTP Status*), pengguna perlu menambahkan deskripsi di belakang kode. Misalnya `999 Invalid Request`. `999` merupakan HTTP Status yang tidak standard. Kode tersebut dapat digunakan dengan catatan pengguna menambahkan deskripsi di belakang kode. Jika pengguna tidak menambahkan deskripsi di belakang kode, maka simulator akan memberikan status `500 Internal Server Error`.

Contoh Request:

```http
POST /universal-simulator/token?detail=yes HTTP/1.1 
Host: 127.0.0.1
Authorization: Basic dXNlcm5hbWU6cGFzc3dvcmQ=
Content-type: application/x-www-form-urlencoded
Content-length: 29

grant_type=client_credentials
```

Contoh Respon

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Length: 363

{
    "token_type": "Bearer",
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJZb3VyIENvbXBhbnkiLCJhdWQiOiJZb3VyIENsaWVudCIsImlhdCI6MTYyMDAyNDI0MiwibmJmIjoxNjIwMDI0MjQyLCJleHAiOjE2MjAwMjc4NDIsImRhdGEiOltdfQ.x_SOclw1fn4irxsNHtX6ai02CTnFAGB5X7O_pRtk5UA",
    "expire_at": 1620027842,
    "expires_in": 3600,
    "email": "token@doconfig1n.tld"
}
```

## Membuat Token

Universal REST Simulator menggunakan *JSON Web Token* atau JWT sebagai metode untuk membuat token. Salah satu kelebihan dari JWT adalah bahwa token dapat divalidasi dengan dirinya sendiri. Server menyimpan beberapa informasi rahasia untuk membuat token dan dapat memvalidasi token yang telah dibuat dengan informasi yang ada pada token tersebut dan informasi rahasia yang disimpan di server. Salah satu informasi rahasia yang disimpan di server yang tidak dimasukkan ke dalam token adalah kunci atau *key* untuk membuat dan memvalidasi token.

JWT mempunyai parameter waktu berlaku. Artinya, sebuah token JWT hanya valid dalam waktu tertentu. Dengan kata lain, apabila token bocor setelah masa berlakunya habis, maka token tersebut tidak dapat digunakan lain oleh siapapun. Tidak ada standard berapa lama masa berlaku JWT. Akan tetapi, banyak yang menggunakan waktu 1 jam untuk masa berlaku token.

Contoh Konfigurasi:

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
{[IF]} (true)\
{[THEN]}\
$OUTPUT.STATUS=403\
$OUTPUT.DELAY=0\
$OUTPUT.BODY={\
}\
{[ENDIF]}\
```

Contoh Request:

```http
POST /auth HTTP/1.1 
Host: 127.0.0.1
Authorization: Basic dXNlcm5hbWU6cGFzc3dvcmQ=
Content-type: application/x-www-form-urlencoded
Content-length: 29

grant_type=client_credentials
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Length: 363

{
    "token_type": "Bearer",
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJZb3VyIENvbXBhbnkiLCJhdWQiOiJZb3VyIENsaWVudCIsImlhdCI6MTYyMDAyNDYxNiwibmJmIjoxNjIwMDI0NjE2LCJleHAiOjE2MjAwMjgyMTYsImRhdGEiOltdfQ.eisS2qFFf4vjifCz7y_d5OyReqtkNSBtBrJoZuwPumw",
    "expire_at": 1620028216,
    "expires_in": 3600,
    "email": "token@doconfig1n.tld"
}
```

## Memvalidasi Token

Untuk memvalidasi token, pengguna cukup menambahkan fungsi `$ISVALIDTOKEN()` pada kondisi yang akan diuji. Saat menemukan fungsi `$ISVALIDTOKEN()`, Universal REST Simulator langsung memproses header `Authorization: Bearer ...` yang ada pada request header dan menguji apakah token yang diberikan valid atau tidak. Jika valid, fungsi `$ISVALIDTOKEN()` akan bernilau `true` namun jika tidak valid, maka fungsi `$ISVALIDTOKEN()` akan bernilai false.

Universal REST Simulator dapat memvalidasi token untuk method `GET`, `POST` maupun `PUT`. Path untuk memvalidasi token tidak ada hubungannya dengan path ketika melakukan request token. Simulator akan memvalidasi token secara independen tanpa terkait dengan path dan header lain.

```ini
PATH=/va-status

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

## Membuat Callback

Callback adalah proses lanjutan yang dilakukan oleh Universal REST Simulator setelah menerima request dari klien. Proses ini berbeda dengan respon. Pada proses asinkron, server akan melakukan request ke endpoint lain setelah menerima request dari klein. Endpoin untuk proses callback bisa ditentukan di konfigurasi server dan bisa pula dikirim oleh klien yang melakukan request ke server. Pada Universal REST Simulator, endpoin callback ditentukan di file konfigurasi.

Selain endpoint callback, ada beberapa parameter callback yang dapat ditentukan di dalam file konfigurasi, di antaranya adalah sebagai berikut:

 1. `$OUTPUT.CALLBACK_URL` adalah URL yang dituju pada proses callback.
 2.  `$OUTPUT.CALLBACK_METHOD` adalah method dari callback. Method yang dapat digunakan adalah `GET`, `POST`, dan `PUT`.
 3.  `$OUTPUT.CALLBACK_TYPE` adalah content type untuk callback. Content type ini bebas sesuai kebutuhan.
 4. `$OUTPUT.CALLBACK_TIMEOUT` adalah timeout untuk callback.
 5. `$OUTPUT.CALLBACK_HEADER` adalah request header untuk callback.
 6. `$OUTPUT.CALLBACK_BODY` adalah request body untuk callback.

Contoh Konfigurasi:

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
	<rc>25</rc>\
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
	<rc>25</rc>\
	<nama>config2</nama>\
	<customer_no>$INPUT.ACCOUNT</customer_no>\
	<product_code>$INPUT.PRODUCT</product_code>\
	<time_stamp>$DATE('j F Y H:i:s', 'UTC+7')</time_stamp>\
	<msg>Pelanggan tidak ditemukan</msg>\
	<refid>$INPUT.REF_NUMBER</refid>\
<data>\
{[ENDIF]}\
```

## Menggunakan Delay

Delay atau sleep digunakan untuk menahan proses selama waktu tertentu. Delay sangat berguna untuk test case time out. Simulator akan melakukan sleep selama waktu tertentu sebelum kemudian mengirimkan respon ke klien. Delay pada Universal REST Simulator diatur pada file konfigurasi sesuai dengan kondisi yang telah ditetapkan. Delay memiliki satuan mili detik.

Contoh Konfigurasi:

```ini
METHOD=POST

PATH=/postjson

REQUEST_TYPE=application/json

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.NAME=$REQUEST.name\
$INPUT.EMAIL=$REQUEST.email\
$INPUT.PHONE=$REQUEST.phone

TRANSACTION_RULE=\
{[IF]} ($INPUT.NAME != "" && $INPUT.EMAIL != "" && $INPUT.PHONE == "081222222222)\
{[THEN]}\
$OUTPUT.DELAY=5000\
$OUTPUT.STATUS=200 OK\
$OUTPUT.BODY={\
    "response_code": "001",\
    "response_text": "Success",\
    "data": {\
        "name": "$INPUT.NAME",\
        "email": "$INPUT.EMAIL",\
        "phone": "$INPUT.PHONE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
{[IF]} ($INPUT.NAME != "" && $INPUT.EMAIL != "" && $INPUT.PHONE == "081222222223)\
{[THEN]}\
$OUTPUT.DELAY=15000\
$OUTPUT.STATUS=200 OK\
$OUTPUT.BODY={\
    "response_code": "001",\
    "response_text": "Success",\
    "data": {\
        "name": "$INPUT.NAME",\
        "email": "$INPUT.EMAIL",\
        "phone": "$INPUT.PHONE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
{[IF]} ($INPUT.NAME != "" && $INPUT.EMAIL != "" && $INPUT.PHONE == "081222222224)\
{[THEN]}\
$OUTPUT.DELAY=0\
$OUTPUT.STATUS=200 OK\
$OUTPUT.BODY={\
    "response_code": "001",\
    "response_text": "Success",\
    "data": {\
        "name": "$INPUT.NAME",\
        "email": "$INPUT.EMAIL",\
        "phone": "$INPUT.PHONE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.STATUS=400 Bad Request\
$OUTPUT.BODY={\
    "response_code": "061",\
    "response_text": "Mandatory field can not be empty",\
    "data": {\
        "name": "$INPUT.NAME",\
        "email": "$INPUT.EMAIL",\
        "phone": "$INPUT.PHONE",\
        "time_stamp": "$DATE('U')"\
    }\
}\
{[ENDIF]}\
```

Pada contoh di atas, apabila parameter `data.phone` memiliki nilai `081222222222`, maka simulator akan melakukan *sleep* selama 5 detik. Apabila parameter `data.phone` memiliki nilai `081222222223`, maka simulator akan melakukan *sleep* selama 15 detik. Apabila parameter `data.phone` memiliki nilai `081222222224`, maka simulator tidak akan melakukan *sleep*. 

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
   "admin": $INPUT.FEE,\
   "total": $CALC($INPUT.AMOUNT + $INPUT.FEE),\
   "transaction_date": "$DATE('d-m-Y H:i:s', 'UTC+9')",\
   "transaction_code": "000002873147"\
}\
{[ENDIF]}\
{[IF]} ($INPUT.PRODUCT == "322112")\
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

## Fungsi $ISVALIDTOKEN()

Fungsi `$ISVALIDTOKEN()` digunakan pada kondisi untuk memvalidasi token yang dikirimkan melalui `Authorization: Bearer`. Simulator akan mengambil token yang dikirimkan melalui header dengan key `Autorization`. Token ini kemudian akan divalidasi sesuai dengan konfigurasi server. Apabila token tersebut benar, `$ISVALIDTOKEN()` akan bernilai `true`. Sebaliknya, apabila token tersebut salah, `$ISVALIDTOKEN()` akan bernilai `false`. Simulator hanya akan memvalidasi token yang dibuat oleh simulator itu sendiri.

## Fungsi $NUMBERFORMAT()

Fungsi `$NUMBERFORMAT()` digunakan untuk memformat suatu bilangan. Jumlah parameter pada fungsi ini bisa 1, 2 atau 3. Fungsi ini identik dengan fungsi `number_format` pada PHP. Tutorial fungsi ini dapat dibaca di https://www.php.net/manual/en/function.number-format.php

Nilai balikan atau output dari fungsi ini bertipe string. Perlu dicatat bahwa Universal REST Simulator bekerja pada mode teks. Dengan demikian, output dari fungsi `$NUMBERFORMAT()` pada JSON wajib diberi tanda kutip. 

Contoh penggunaan fungsi ini adalah `$NUMBERFORMAT($INPUT.AMOUNT, 2, ',', '.')` di mana `$INPUT.AMOUNT` adalah nilai yang akan ditampilkan.

Contoh Konfigurasi:
```ini
METHOD=POST

PATH=/basicauth

REQUEST_TYPE=application/json

RESPONSE_TYPE=application/json

PARSING_RULE=\
$INPUT.USERNAME=$AUTHORIZATION_BASIC.USERNAME\
$INPUT.PASSWORD=$AUTHORIZATION_BASIC.PASSWORD\
$INPUT.ACCOUNT_NUMBER=$REQUEST.data.account_number\
$INPUT.AMOUNT=$REQUEST.data.amount\
$INPUT.CURRENCY=$REQUEST.data.currency_code

TRANSACTION_RULE=\
{[IF]} ($INPUT.USERNAME == "user1" && $INPUT.PASSWORD == "password1" && $INPUT.ACCOUNT_NUMBER != "" && $INPUT.AMOUNT > 0)\
{[THEN]}\
$OUTPUT.STATUS=200\
$OUTPUT.BODY={\
    "action": "$INPUT.TRANSACTION_TYPE",\
    "id": 69,\
    "data": {\
        "date_time": "$INPUT.DATE_TIME",\
        "name": "Bambang",\
        "account_number": "$INPUT.ACCOUNT_NUMBER",\
        "amount": "$NUMBERFORMAT($INPUT.AMOUNT, 2, ',', '.')",\
        "currency_code": "$INPUT.CURRENCY"\
    },\
    "response_code": "001",\
    "response_text": "Success"\
}\
{[ENDIF]}\
{[IF]} (true)\
{[THEN]}\
$OUTPUT.STATUS=403\
$OUTPUT.BODY={\
    "action": "$INPUT.TRANSACTION_TYPE",\
    "id": "$INPUT.TRANSACTION_ID",\
    "data": {},\
    "response_code": "062",\
    "response_text": "Access forbidden"\
}\
{[ENDIF]}\
```

Contoh Request:

```http
GET /basicauth HTTP/1.1 
Host: 127.0.0.1
User-Agent: Service
Content-Type: application/json
Accept: application/json
Content-Length: 118
Authorization: Basic dXNlcjE6cGFzc3dvcmQx

{
    "data": {
        "account_number": "98765432",
        "amount": 250000,
        "currency_code": "IDR"
    }
}
```

Contoh Respon:

```http
HTTP/1.1 200 OK
Content-Type: application/json
Content-Lengh: 291

{
    "action": "deposit",
    "id": 69,
    "data": {
        "date_time": "2021-05-01T10:11:12.000Z",
        "name": "Bambang",
        "account_number": "123456",
        "amount": "250,000.00",
        "currency_code": "IDR"
    },
    "response_code": "001",
    "response_text": "Success"
}
```

