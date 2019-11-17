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

    let objClone = JSON.parse(JSON.stringify(obj));

    /* adopt data */
    window.categories.data = objClone.categories;
    window.classes.data = objClone.classes;

    /* build objClone */
    objClone.categories = mergeData(window.categories);
    objClone.classes = mergeData(window.classes);

    /* build json string */
    let data = JSON.stringify(objClone);

    /* send and save json string */
    xhr.send(data);
}

function splitData(data, start, max)
{
    /* Correct the max value if -1 given */
    max = max === -1 ? data.length : max;

    /* Correct the max value is to hight */
    max = start + max > data.length ? data.length - start : max;

    return {
        'before': start === 0 ? [] : data.slice(0, start),
        'data': data.slice(start, start + max),
        'after': start + max > data.length ? [] : data.slice(start + max)
    };
}

function mergeData(obj) {
    let data = [];

    data = data.concat(obj.before);
    data = data.concat(obj.data);
    data = data.concat(obj.after);

    return data;
}

function startEditor()
{
    /* -1: all */
    let startCategory = 0;
    let maxCategories = 2;
    let startClass = 0;
    let maxClasses = 2;

    loadJSON('data/ml.json', function(response) {
        let jsonData = JSON.parse(response);

        window.categories = splitData(jsonData.categories, startCategory, maxCategories);
        window.classes = splitData(jsonData.classes, startClass, maxClasses);

        /* adopt the wanted data */
        jsonData.categories = window.categories.data;
        jsonData.classes = window.classes.data;

        /* Initialize the editor */
        let editor = new JSONEditor(document.getElementById('editor_holder'),{
            ajax: true,
            schema: {
                $ref: "json/ml.json",
                format: "grid"
            },
            startval: jsonData
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
            editor.setValue(jsonData);
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
