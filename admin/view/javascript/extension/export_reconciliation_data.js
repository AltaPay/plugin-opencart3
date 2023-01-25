/*
 * order_export_import.js
 * Copyright (C) 2018 tim <tim@tim-PC>
 *
 * Distributed under terms of the MIT license.
 */
(function(){
    'use strict';
    $(function(){
        var token = getURLVar('user_token');
        var filter_btn = $('#button-filter');
        var form = $('#filter-order');
        var export_btn = $(' <button class="btn btn-default"><i class="fa fa-download"></i> Export Reconciliation Data</button> ');
        export_btn.on('click', function(){
            document.location = getExportUrl(form,token);
            return false;
        })
        filter_btn.after(export_btn);

    });

    var getExportUrl= function (form, token){
        var  baseUrl = $('base',document).attr('href');
        baseUrl +='index.php';
        baseUrl = setURLVar('route', 'extension/module/altapay/exportReconciliationData', baseUrl);
        baseUrl = setURLVar('user_token', token, baseUrl);

        var inputs = $('input,select', form)
        $.each(inputs, function(i,v){
            var input = $(v);
            baseUrl = setURLVar(input.attr('name'), input.val(), baseUrl);
        });
        console.log(baseUrl)
        return baseUrl;
    }
    function getURLVar(key, url) {
        url = url || String(document.location);
        var value = [];
        var hash_position = url.indexOf('#');
        if (hash_position >= 0) {
            url = url.substring(0, hash_position);
        }
        var query = url.split('?');

        if (query[1]) {
            var part = query[1].split('&');

            for (var i = 0; i < part.length; i++) {
                var data = part[i].split('=');

                if (data[0] && data[1]) {
                    value[data[0]] = data[1];
                }
            }

            if (value[key]) {
                return value[key];
            } else {
                return '';
            }
        }
    }
    function setURLVar(key, value, url) {
        url = url || String(document.location);
        var rows = {};
        var query = url.split('?');
        if (query[1]) {
            var part = query[1].split('&');

            for (var i = 0; i < part.length; i++) {
                var data = part[i].split('=');

                if (data[0] && data[1]) {
                    rows[data[0]] = data[1];
                } else if (data[0]) {
                    rows[data[0]] = '';
                }
            }
            rows[key] = encodeURIComponent(value);
            var q = [];
            for (var k in rows) {
                var v = rows[k];
                q.push('' + k + '=' + v);
            }
            query[1] = q.join('&');
        } else if (query[0]) {
            query[1] = '' + key + '=' + value;
        }
        return query.join('?');
    }

})();