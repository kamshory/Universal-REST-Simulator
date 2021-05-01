# Sekilas Tentang Universal REST Simulator

Universal REST Simulator adalah simulator REST untuk membuat simulator server aplikasi. Simulator ini akan mensimulasikan respon dari sebuah sistem saat diberi request tertentu.

Universal REST Simulator menggunakan protokol HTTP dengan method GET, POST dan PUT dengan tipe request x-www-urlencode, JSON dan XML. Tipe respon dapat berupa text, HTML, XML, JSON maupun CSV.

# File Manager

File manager pada Universal REST Simulator digunakan utuk membuat, mengubah dan mengatur file konfigurasi simulator. Untuk dapat membuat dan mengatur file konfigurasi simulator, pengguna harus login ke file manager. Username dan password pengguna disimpan dalam file .htpasswd yang disimpan di direktori filemanager di dalam direktori simulator.

Untuk mengakses file manager, buka Universal REST Simulator dengan menggunakan browser web dan masukkan path `/filemanager` relatif terhadap path Universal REST Simulator.

# Check Path

Check path digunakan untuk melihat path dan method yang ada pada semua file konfigurasi. Tujuan dari check path adalah sebagai berikut:

1. menghindari konflik path dan method
2. memudahkan dalam pencarian file konfigurasi untuk keperluan perubahan dan pembaruan
3. jalan pintas untuk mengubah dan memperbarui file konfigurasi

# File Konfigurasi

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

# Simulator Sederhana GET application/x-www-form-urlencoded

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

# Simulator Sederhana POST application/x-www-form-urlencoded

```ini
METHOD=POST

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

# Simulator Sederhana PUT application/x-www-form-urlencoded

```ini
METHOD=PUT

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

# Simulator Sederhana POST application/json

```ini
METHOD=POST

PATH=/getdata

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

# Simulator Sederhana PUT application/json

```ini
METHOD=PUT

PATH=/getdata

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


