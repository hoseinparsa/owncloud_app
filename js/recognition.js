$(document).ready(function () {
    if (!OCA.Files) return;

    var BaseUrl = OC.generateUrl('apps/recognition');
    var BaseUrlFile = OC.generateUrl('apps/files');
    var webroot = OC.webroot
    var dialog = OCdialogs
    var dataURL = oc_dataURL
    var webroot = oc_webroot
    var appswebroots = oc_appswebroots
    var filesdir = webroot + '/' + oc_dataURL
    var host = OC.getHost()
    var appid = 'recognition',
        $dir = $('#dir'),
        $fileList = $('#fileList');


    var $add = $('<a/>')
        .attr('id', 'recognition_add')
        .append($('<img/>').addClass('add').attr('src', OC.imagePath(appid, 'add')))
        .append(' ')
        .on('click', function () {
            add_emp()
        })
        .hide()
        .appendTo('#headerName .selectedActions');

    var $regocnition = $('<a/>')
        .attr('id', 'recognition_rec')
        .append($('<img/>').addClass('rec').attr('src', OC.imagePath(appid, 'rec')))
        .append(' ')
        .on('click', function () {
            recognition()
        })
        .hide()
        .appendTo('#headerName .selectedActions');

    // add employee //
    // seperate file and folder
    function add_emp(file) {
        var files = file ? [file] : FileList.getSelectedFiles().map(function (file) {
            return file;
        });

        for (i = 0; i < files.length; i++) {
            if (files[i].type == 'file') {
                OCdialogs.info('Image sent, wait for processing to be finished.', 'Sending image')
                get_file(files[i])
            } else {
                OCdialogs.info('Sending data started, please wait ...', 'Sending folder info')
                get_file_dir(files[i])
            }
        }
    }

    // send file to server
    function get_file(file) {
        var data = {
            directory: file.path,
            datadir: filesdir,
            files: [file],
            permissions: 31
        };
        $.ajax({
            url: BaseUrl + '/api/v1/AddEmp',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data)
        }).done(function (response) {
            //return response
            OCdialogs.info('Data was successfully sent.', 'Success')
        }).fail(function (response, code) {
            // handle failure
            OCdialogs.info('data dont send' + code, 'Error')

        });

    }

    // get dir files
    function get_file_dir(path) {
        var path = path['path'] + '/' + path['name']
        $.ajax({
            url: BaseUrlFile + '/ajax/list.php?dir=' + path,
            type: 'GET',
            contentType: 'application/json',
        }).done(function (response) {
            // handle success
            //OCdialogs.info('Start To Send Data And Start To Proccessing.Please Wait!','Success')
            send_dir(response.data)
        }).fail(function (response, code) {
            OCdialogs.info('data dont send' + code, 'Error')
            // handle failure
        });

    }

    // send dir files to server
    function send_dir(data) {
        var data_dir = {
            directory: data.directory,
            datadir: filesdir,
            files: data.files,
            permissions: data.permissions
        };
        $.ajax({
            url: BaseUrl + '/api/v1/AddEmp',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data_dir)
        }).done(function (response) {
            //return response
            OCdialogs.info('Data was successfully sent.', 'Success')
        }).fail(function (response, code) {
            // handle failure
            OCdialogs.info('data dont send' + code, 'Error')
        });

    }

    // end add employee //

    // recognition //
    // seperate files and folder
    function recognition(file) {
        var files = file ? [file] : FileList.getSelectedFiles().map(function (file) {
            return file;
        });
        for (i = 0; i < files.length; i++) {
            if (files[i].type == 'file') {
                OCdialogs.info('Sending data started, please wait ...', 'Sending image')
                get_file_rec(files[i])
            } else {
                OCdialogs.info('Sending data started, please wait ...', 'Sending folder info')
                get_file_dir_rec(files[i])
            }
        }
    }

    // send file to proccess
    function get_file_rec(file) {
        var data = {
            directory: file.path,
            files: [file],
            permissions: 31,
            datadir: filesdir
        };
        $.ajax({
            url: BaseUrl + '/api/v1/Recognition',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data)
        }).done(function (response) {
            //return response
            FilterFile(response)
        }).fail(function (response, code) {
            // handle failure
            OCdialogs.info('data dont send' + code, 'Error')
        });
    }

    // get files in dir
    function get_file_dir_rec(path) {
        var path = path['path'] + '/' + path['name']
        $.ajax({
            url: BaseUrlFile + '/ajax/list.php?dir=' + path,
            type: 'GET',
            contentType: 'application/json',
        }).done(function (response) {
            // handle success
            send_dir_rec(response.data)
        }).fail(function (response, code) {
            // handle failure
        });
    }

    // send files in dir to proccess
    function send_dir_rec(data) {
        var data_dir = {
            directory: data.directory,
            datadir: filesdir,
            files: data.files,
            permissions: data.permissions
        };

        $.ajax({
            url: BaseUrl + '/api/v1/Recognition',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data_dir)
        }).done(function (response) {
            //handle response
            FilterFile(response)
        }).fail(function (response, code) {
            // handle failure
            OCdialogs.info('data dont send' + code, 'Error')
        });

    }

    // end of recognition //

    // filter files and clean some params
    function FilterFile(file) {
        for (i = 0; i < file.data.length; i++) {
            if (file.data[i].status == 'True') {
                var parsefile = JSON.parse(file.data[i].people)
                var object = JSON.parse(file.data[i].the_objects)
                if (parsefile.length != 0) {
                    for (j = 0; j < parsefile.length; j++) {
                        var name = parsefile[j]['employee_name']
                        check_tags(name, file.id[i], name)
                    }
                } else {
                    OCdialogs.info('Please add a person to database.', 'Incomplete data Error')
                }

                if (object.length != 0) {
                    for (j = 0; j < object.length; j++) {
                        if (object[j] != 'Object Detection Status is False') {
                            var name = object[j]
                            check_tags(name, file.id[i], name)
                        }
                    }
                } else {
                    OCdialogs.info('No tag generated.', 'Error')
                }
            } else {
                OCdialogs.info('No face was recognized.', 'Error')
            }
        }
        OCdialogs.info('Tagging Process finished successfully.', 'Tagging success')
    }

    // get files tag id and set to function to tagging
    async function check_tags(tagname, fileid, tag) {
        var url = 'http://' + host + OC.webroot + '/remote.php/dav/systemtags/'
        var method = 'PROPFIND'
        var headers = {
            'contentType': 'application/xml; charset=utf-8',
            'X-Requested-With': "XMLHttpRequest",
            'Depth': "1",
            'requesttoken': oc_requesttoken,
        }
        var id;
        var data = '<?xml version="1.0" encoding="utf-8" ?><a:propfind xmlns:a="DAV:" xmlns:oc="http://owncloud.org/ns"><a:prop><oc:display-name/><oc:user-visible/><oc:user-assignable/><oc:id/></a:prop></a:propfind>'
        var id_tag = dav.Client.prototype.request(method, url, headers, data).then(function (result) {
            for (i = 0; i < result.body.length; i++) {
                if (result.body[i].propStat[0].properties['{http://owncloud.org/ns}display-name'] == tagname) {
                    return result.body[i].propStat[0].properties['{http://owncloud.org/ns}id']
                }
            }
        });
        set_tag(fileid, id_tag, tag)
    }

    // tagging files if tag exist  else set tag from server params
    function set_tag(fileid, id_tag, tag) {
        id_tag.then(function (result) {
            if (result) {
                $.ajax({
                    url: webroot + '/remote.php/dav/systemtags-relations/files/' + fileid + '/' + result,
                    type: 'PUT',
                    contentType: 'application/json',
                }).done(function (response) {
                    //return response
                    //OCdialogs.info('Tagging Process Successful.  FileName:'+tag,'Success Tagging')
                }).fail(function (response) {
                    // handle failure
                    OCdialogs.info('This File ' + tag + ' already exists.', 'Conflict')
                });

            } else {
                var data = {
                    name: tag,
                    userVisible: "true",
                    userAssignable: "true",
                    userEditable: "true",
                    canAssign: "true"
                };
                $.ajax({
                    url: OC.webroot + '/remote.php/dav/systemtags-relations/files/' + fileid,
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data)
                }).done(function (response) {
                    //return response
                    // OCdialogs.info('Tagging Process Successful.  FileName:'+tag,'Success Tagging')
                }).fail(function (response, code) {
                    // handle failure
                    if (response.status == '409') {
                        OCdialogs.info('This File ' + tag + ' already exists.', 'Conflict')
                    }
                });
            }
        })
    }

    function update(blink) {
        var permissions = parseInt($('#permissions').val());
        $add.toggle((permissions & OC.PERMISSION_READ && permissions & OC.PERMISSION_UPDATE) !== 0);
        $regocnition.toggle((permissions & OC.PERMISSION_READ && permissions & OC.PERMISSION_UPDATE) !== 0);
    };

    $fileList.on('updated', update);
});
