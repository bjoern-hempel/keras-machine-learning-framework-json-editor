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

function setInformation(name, data, dataAll, page, start, max)
{
    if (max > 0 && page > 0) {
        let from = data.before.length + 1;
        let to = data.before.length + max;
        let all = dataAll.length;

        to = to > all ? all : to;

        let maxPage = Math.ceil(dataAll.length / max);
        let nameCamelcase = name.charAt(0).toUpperCase() + name.slice(1);
        let linkPrevious = '';
        let linkNext = '';

        /* previous link */
        if (page > 1) {
            let parameterPrevious = '?page%name=%page'.replace(/%name/, nameCamelcase).replace(/%page/, page - 1);
            linkPrevious = '<a href="%parameter"> - </a>'.replace(/%parameter/, parameterPrevious);
        }

        /* next link */
        if (page < maxPage) {
            let parameterNext = '?page%name=%page'.replace(/%name/, nameCamelcase).replace(/%page/, page + 1);
            linkNext = '<a href="%parameter"> + </a>'.replace(/%parameter/, parameterNext);
        }

        /* current string */
        let currentString = '%linkPrevious%page%linkNext'.
            replace(/%linkPrevious/, linkPrevious).
            replace(/%linkNext/, linkNext).
            replace(/%page/, String(page));

        document.getElementById('%s-page-current'.replace(/%s/, name)).innerHTML = currentString;
        document.getElementById('%s-page-all'.replace(/%s/, name)).innerText = String(maxPage);
        document.getElementById('%s-from'.replace(/%s/, name)).innerText = String(from);
        document.getElementById('%s-to'.replace(/%s/, name)).innerText = String(to);
        document.getElementById('%s-all'.replace(/%s/, name)).innerText = String(all);
    }
}

function getLabelsArray() {
    let labels = [];

    $("div.panel > div > div[data-schemapath*='root.classes.'] > h3 > label").each(function (index) {
        let label = $(this);

        labels.push({
            'label': label,
            'name': label.parent().parent().find("input[name*='[class]']").val()
        });
    });

    return labels;
}

function startEditor()
{
    /* Default parameter for category and class */
    let defaultPageCategory = 1;
    let defaultStartCategory = 0;
    let defaultMaxCategories = 10;
    let defaultPageClass = 1;
    let defaultStartClass = 0;
    let defaultMaxClasses = 10;

    /* Read url */
    let url = new URL(window.location.href);

    /* Category => -1: all */
    let pageCategory = url.searchParams.get('pageCategory') === null ? defaultPageCategory : parseInt(url.searchParams.get('pageCategory'));
    let startCategory = url.searchParams.get('startCategory') === null ? defaultStartCategory : parseInt(url.searchParams.get('startCategory'));
    let maxCategories = url.searchParams.get('maxCategories') === null ? defaultMaxCategories : parseInt(url.searchParams.get('maxCategories'));
    startCategory += (pageCategory - 1) * maxCategories;

    /* Class => -1: all */
    let pageClass = url.searchParams.get('pageClass') === null ? defaultPageClass : parseInt(url.searchParams.get('pageClass'));
    let startClass = url.searchParams.get('maxCategories') === null ? defaultStartClass : parseInt(url.searchParams.get('maxCategories'));
    let maxClasses = url.searchParams.get('maxCategories') === null ? defaultMaxClasses : parseInt(url.searchParams.get('maxCategories'));
    startClass += (pageClass - 1) * maxClasses;

    loadJSON('data/ml.json', function(response) {
        let jsonData = JSON.parse(response);

        /* Split data */
        window.categories = splitData(jsonData.categories, startCategory, maxCategories);
        window.classes = splitData(jsonData.classes, startClass, maxClasses);

        /* Set category and class labels */
        setInformation('category', window.categories, jsonData.categories, pageCategory, startCategory, maxCategories);
        setInformation('class', window.classes, jsonData.classes, pageClass, startClass, maxClasses);

        /* Adopt the wanted data */
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

        editor.on('ready',function() {
            let counter = startClass + 1;
            let labels = getLabelsArray();
            labels.forEach(function (label, index) {
                let html = '<a class="anchor" id="%name"></a>Class %number: "%name"'.
                    replace(/%name/g, label.name).
                    replace(/%number/, counter);

                if (labels.length > 1) {

                    /* Button up */
                    if (index > 0) {
                        let labelPrevious = labels[index - 1];
                        html += `
                            <button type="button" title="Go to previous class %name" class="button tiny json-editor-btn-moveup moveup json-editor-btntype-up"
                                onclick="window.location.href='#%name'"
                            >
                                <i class="fa fa-arrow-up"></i><span> </span>
                            </button>
                        `.replace(/%name/g, labelPrevious);
                    }

                    /* Button down */
                    if (index + 1 < labels.length) {
                        let labelNext = labels[index + 1];
                        html += `
                            <button type="button" title="Go to next class %name" class="button tiny json-editor-btn-movedown movedown json-editor-btntype-move"
                                onclick="window.location.href='#%name'"
                            >
                                <i class="fa fa-arrow-down"></i><span> </span>
                            </button>
                        `.replace(/%name/g, labelNext.name);
                    }
                }

                label.label.html(html);
                counter++;
            });
        });


    });
}
