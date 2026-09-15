// Filter form behaviour shared by the change log pages (systemChanges, projectChanges, userRoleChanges).
// Loaded after the form, so the date inputs and buttons already exist.

//gets the date format to use from the built-in format from REDCap for use with js rather than the
//PHP format used for the data-df attribute
let dateFormat = user_date_format_jquery

$('#startdt').datetimepicker({
    dateFormat: dateFormat,
    showOn: 'button', buttonImage: app_path_images+'date.png',
    onClose: function () {
        if(document.getElementById('startdt').value) {
            document.getElementById('defaulttimefilter').value = 'customrange';
            submitForm('startdt');
        }
    }
});
$('#enddt').datetimepicker({
    dateFormat: dateFormat,
    showOn: 'button', buttonImage: app_path_images+'date.png',
    onClose: function () {
        if(document.getElementById('enddt').value) {
            document.getElementById('defaulttimefilter').value = 'customrange';
            submitForm('enddt');
        }
    }
});

// downloads the CSV export, keeping the current page's filters; projId is empty on the System Changes page
function cleanUpParamsAndRun(moduleName, projId, exportType, tableName) {
    //construct the params from the current page params
    let finalUrl = app_path_webroot+'ExternalModules/?prefix=' + moduleName + '&page=csv_export';
    if (projId) {
        finalUrl += '&pid=' + projId;
    }

    let params = new URLSearchParams(window.location.search);
    //ignore some params
    params.forEach((v, k) => {
        if(k !== 'prefix' && k !== 'page' && k !== 'pid' && k !== 'redcap_csrf_token' ) {
            finalUrl += '&' + k + '=' + encodeURIComponent(v);
        }
    });

    //add the param to determine what to export
    finalUrl += '&export_type=' + exportType;
    finalUrl += '&tableName=' + tableName;

    window.location.href=finalUrl;
}

// returns the page to its default state
function resetForm() {
    showProgress(1);
    window.location.href = document.getElementById('filterForm').dataset.resetUrl;
}

function setCustomRange() {
    document.getElementById('defaulttimefilter').value = 'customrange';
    document.querySelector('#startdt + button').click();
}

function setTimeFrame(timeframe) {
    document.getElementById('startdt').value = document.getElementById(timeframe).value;
    document.getElementById('enddt').value = '';
    document.getElementById('defaulttimefilter').value = timeframe;
    resetPaging();
    submitForm('startdt');
}

function nextPage() {
    let currPage = document.getElementById('pagenum');
    let totPages = document.getElementById('totpages');
    if (currPage.value < totPages.value) {
        currPage.value = Number(currPage.value) + 1;
        submitForm('pagenum');
    }
}

function prevPage() {
    let currPage = document.getElementById('pagenum');
    if(currPage.value > 0) {
        currPage.value = Number(currPage.value) - 1;
        submitForm('pagenum');
    }
}

function resetPaging() {
    let currPage = document.getElementById('pagenum');
    currPage.value = 0;
    let totPages = document.getElementById('totpages');
    totPages.value = 0;
}

function onDirectionChanged() {
    submitForm('retdirection');
}

function onFilterChanged(id) {
    resetPaging();
    submitForm(id);
}

// use this when a field changes so can run request on any change
function submitForm(src) {
    showProgress(1);

    let frm = document.getElementById('filterForm');
    //clear the csrfToken
    let csrfToken = document.querySelector('input[name="redcap_csrf_token"]');
    csrfToken.value = '';
    frm.submit();
}

function resetDate(dateId) {
    if(document.getElementById(dateId).value) {
        document.getElementById(dateId).value = '';
        document.getElementById('defaulttimefilter').value = 'customrange';
        submitForm(dateId);
    }
}

$(window).on('load', function() {

    //handle disabling nav buttons when not applicable
    let currPage = document.getElementById('pagenum');
    let totPages = document.getElementById('totpages');

    document.getElementById('btnprevpage').disabled = currPage.value === '0';
    document.getElementById('btnnextpage').disabled = parseInt(currPage.value) + 1 === parseInt(totPages.value);

});
