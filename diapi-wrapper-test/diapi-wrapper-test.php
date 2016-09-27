<?php

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>BCDIAPI Wrapper Test</title>
        <style>
          body {
              font-family: sans-serif;
              color: #333;
              padding: 6em;
          }
        </style>
    </head>
    <body>
    <h1>BCDIAPI Wrapper Test</h1>

    <div class="form-wrapper">
        <fieldset>
            <legend>Input</legend>
                <p>
                Account id:
                <input name="account" id="account" type="text" value="20318290001">
            </p>
                <p>
                    Client id:
                    <input name="client_id" id="client_id" type="text" value="">
                </p>
                <p>
                    Client secret:
                    <input name="client_secret" id="client_secret" type="text" value="">
                </p>
                <p>
                    Select video for <strong>pull-based</strong> ingestion:
                    <select id="pullVideoSelect">
                        <option value=""></option>
                        <option value=""></option>
                        <option value=""></option>
                        <option value=""></option>
                    </select>
                </p>
                <p>
                    Call body (optional - include if you need to submit data with the request):<br />
                    <textarea name="requestBody" id="requestBody"></textarea>
                </p>
                <p>
                    Full request URL for the API call:<br />
                    <textarea name="url" id="cmsRequest"></textarea>
                </p>
                <p>
                    <input id="ajaxSubmit" type="submit" value="Submit">
                </p>
            <pre><code id="response"></code></pre>
        </fieldset>
    </div>
        <script>

        </script>
    </body>
</html>
