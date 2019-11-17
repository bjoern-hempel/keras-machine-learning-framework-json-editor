function loadJSON(jsonPath, callback)
{
    let request = new XMLHttpRequest();
    request.overrideMimeType("application/json");
    request.open('GET', jsonPath, true); // Replace 'my_data' with the path to your file
    request.onreadystatechange = function () {
        if (request.readyState === 4 && request.status === 200) {
            callback(request.responseText);
        }
    };
    request.send(null);
}

function saveJSON(obj, callback)
{
    let xhr = new XMLHttpRequest();
    let url = "/php/write-json.php";
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            let json = JSON.parse(xhr.responseText);
            callback(json);
        }
    };
    let data = JSON.stringify(obj);
    xhr.send(data);
}

function startEditor()
{
    /* -1: all */
    let maxCategories = -1;

    loadJSON('data/ml.json', function(response) {
        let json_data = JSON.parse(response);

        if (maxCategories > -1) {
            json_data.categories = json_data.categories.slice(0, maxCategories);
        }

        /* Initialize the editor */
        let editor = new JSONEditor(document.getElementById('editor_holder'),{
            ajax: true,
            schema: {
                $ref: "json/ml.json",
                format: "grid"
            },
            startval: json_data
        });

        /* Hook up the submit button to log to the console */
        document.getElementById('submit').addEventListener('click',function() {
            saveJSON(editor.getValue(), function (json) {
                console.log('Save result.', json);
                alert('Saved');
            })
        });

        /* Hook up the Restore to Default button */
        document.getElementById('restore').addEventListener('click',function() {
            editor.setValue(json_data);
        });

        /* Hook up the validation indicator to update its status whenever the editor changes */
        editor.on('change',function() {
            let errors = editor.validate();
            let indicator = document.getElementById('valid_indicator');

            if (errors.length) {
                indicator.className = 'label alert';
                indicator.textContent = 'not valid';
            } else {
                indicator.className = 'label success';
                indicator.textContent = 'valid';
            }
        });
    });
}
