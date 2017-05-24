<?php
if (! empty ( $_POST ['action'] )) {
	header ( "content-type: text/plain;charset=utf8" );
	if (! isset ( $_POST ['value'] )) {
		http_response_code ( 400 );
		die ( 'no value provided!' );
	}
	$value = $_POST ['value'];
	if (! isset ( $_POST ['password'] )) {
		http_response_code ( 400 );
		die ( 'no password provided!' );
	}
	$password = $_POST ['password'];
	require_once ('hhb_crypted.inc.php');
	switch ($_POST ['action']) {
		case 'encrypt' :
			{
				echo hhb_encrypt ( $value, $password );
				break;
			}
		case 'decrypt' :
			{
				echo hhb_decrypt ( $value, $password );
				break;
			}
		default :
			{
				http_response_code ( 400 );
				echo 'unknown action: ';
				var_dump ( $_POST ['action'] );
				die ();
				break;
			}
	}
	die ();
}

?>
<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<title>cryptage</title>
<script
	src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.11.0/styles/default.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.11.0/highlight.min.js"></script>
</head>

<body>
	<span> warning, everything written here is sent over http(s?) to some
		server for encryption/decryption.<br /> i *should* write a full
		javascript-encrypter/decrypter when i get time..
	</span>
	<br /> encryption password:
	<input type="password" id="encryptPassword" />
	<br />
	<textarea id="encryptInput" placeholder="write text to encrypt here."></textarea>
	<br />
	<button id="encrypt">encrypt</button>
	<br />
	<pre id="encrypted">encrypted text comes here</pre>
	<script>
            "use strict";
            document.getElementById("encrypt").addEventListener("click",
                function() {
                    var input = document.getElementById("encryptInput").value;
                    var password = document.getElementById("encryptPassword").value;
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "?");
                    xhr.addEventListener("readystatechange", function(ev) {
                        var xhr = ev.target;
                        if (xhr.readyState < 4) {
                            return;
                        }
                        document.getElementById("encrypted").textContent = xhr.responseText;
                    });
                    var fd = new FormData();
                    fd.append("action", "encrypt");
                    fd.append("value", input);
                    fd.append("password", password);
                    xhr.send(fd);
                });
        </script>
	<br /> decryption password:
	<input type="password" id="decryptPassword" />
	<br />
	<textarea id="decryptInput"
		placeholder="write text here for decryption"></textarea>
	<br />
	<button id="decrypt">decrypt</button>
	<br />
	<pre id="decrypted">decrypted text comes here.</pre>
	<script>
            "use strict";
            document.getElementById("decrypt").addEventListener("click",
                function() {
                    var input = document.getElementById("decryptInput").value;
                    var password = document.getElementById("decryptPassword").value;
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "?");
                    xhr.addEventListener("readystatechange", function(ev) {
                        var xhr = ev.target;
                        if (xhr.readyState < 4) {
                            return;
                        }
                        document.getElementById("decrypted").textContent = xhr.responseText;
                    });
                    var fd = new FormData();
                    fd.append("action", "decrypt");
                    fd.append("value", input);
                    fd.append("password", password);
                    xhr.send(fd);
                });
        </script>
</body>

</html>