

# Tutorial Menggunakan Universal REST Simulator

## Sekilas Tentang Universal REST Simulator

Universal REST Simulator adalah simulator REST untuk membuat simulator server aplikasi. Simulator ini akan mensimulasikan respon dari sebuah sistem saat diberi request tertentu.

Universal REST Simulator menggunakan protokol HTTP dengan method GET, POST dan PUT dengan tipe request x-www-urlencode, JSON dan XML. Tipe respon dapat berupa text, HTML, XML, JSON maupun CSV.

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

Konfigurasi simulator diatur oleh file-file yang disimpan di dalam direktori `/config` relatif terhadap direktori Universal REST Simulator. Pada saat simulator menerima request dari klien, simulator akan mencari file konfigurasi yang cocok dengan method dan path dari request yang diterima. Apabila simulator menemukan file yang sesuai, maka simulator akan berhenti mencari file dan menggunakan konfigurasi pada file yang tersebut.

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

Path adalah path yang diakses oleh klien. Path ini bersifat relatif. Dalam beberapa kondisi mungkin membutuhkan path yang sama persis namun dalam kondisi yang lain hanya memerlukan kecocokan pola.

Path juga dapat berisi input dari klien. Dengan demikian, path request yang berbeda mungkin akan menjalankan proses yang sama.

Contoh:

```ini
PATH=/core/{[GROUP]}/{[TRANSACTION]}
```

**REQUEST_TYPE**

Request type adalah tipe data dari request yang dikirimkan oleh klien. Tipe data request akan menentukan bagaimaka cara simulator memparsing request dari klien. Ketidaksesuaian antara request type pada konfigurasi dengan tipe data yang dikirimkan akan menyebabkan data tidak dapat diparsing sama sekali.

Tipe data yang didukung adalah sebagai berikut:

1. `application/x-www-form-urlencoded` untuk method `GET`, `POST` dan `PUT`
2. `application/xml` untuk method `POST` dan `PUT`
3. `application/json` untuk method `POST` dan `PUT`

**RESPONSE_TYPE**

Response type adalah tipe data respon yang dikirimkan ke klien. Tipe data ini akan diinformasikan melalui header respon `Content-type`. Tipe data ini mengabaikan header request `Accept` yang dikirimkan oleh klien. Apabila pengguna ingin menggunakan nilai pada header request `Accept`, pengguna dapat membuat kondisi dengan terlebih dahulu mengambil nilai `Accept` pada parsing rule sebagai berikut:

```ini
PARSING_RULE=\
$INPUT.ACCEPT=$HEADER.ACCEPT
```

Kemudian menambahkan kondisi `$INPUT.ACCEPT` pada transaction rule.

Tipe data yang didukung lebih luas meskipun terbatas pada tipe data text misalnya sebagai berikut:

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
4. `$GET` yaitu nilai yang diambil dari parameter yang dikirimkan melalui URL dengan pengkodean `x-www-urlencode`
5. `$POST` yaitu nilai yang diambil dari body request dengan method `POST` dengan pengkodean `x-www-urlencode`
6. `$PUT` yaitu nilai yang diambil dari body request dengan method `PUT` dengan pengkodean `x-www-urlencode`
7. `$REQUEST` yaitu alternatif dari `$GET`, `$POST` dan `$PUT` tergantung dari method yang digunakan pada file konfigurasi

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
$INPUT.ACCOUNT_NUMBER=$REQUEST.data.account_number\
$INPUT.AMOUNT=$REQUEST.data.amount\
$INPUT.CURRENCY=$REQUEST.data.currency_code

TRANSACTION_RULE=\
{[IF]} ($INPUT.DATE_TIME != "" && $INPUT.SIGNATURE != "" && $INPUT.ACCOUNT_NUMBER != "" && $INPUT.AMOUNT > 0)\
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

