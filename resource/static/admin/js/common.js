$(document).ready(function () {
    //点击弹出模态框
    $('body').on('click','.clickModal',function(){
        var url= $(this).attr("data-url");
        var modal_backdrop= $(this).attr("data-backdrop");
        $.get(url,function(result){
            if(result.code && result.code === 500)
            {
                Notify.error(result.info);
                return;
            }
            if(modal_backdrop && modal_backdrop === 'static')
            {
                $("#modal").modal({
                    backdrop: 'static',//点击背景空白处不被关闭；
                    keyboard: false//触发键盘esc事件时不关闭。
                });
            }else{
                $("#modal").modal({
                    backdrop:true,
                    keyboard: false//触发键盘esc事件时不关闭。
                });
            }
            $("#modal").html(result).modal('show');
        }).catch(function (result) {
            Notify.error('请求失败');
        });
    });


    $('body').on('click','.showModal',function(){
        $('body').append('<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"></div>');
        var url= $(this).attr("data-url");
        $.get(url,function(res){
            $('#myModal').html(res).modal();
        })
    })

    //点击确认是否请求，用于如点击确认是否删除数据
    $('body').on('click','.clickConfirm',function(){
        var message = $(this).attr("confirm-message");
        var url = $(this).attr("confirm-url");
        var success = $(this).attr("confirm-success");
        var error = $(this).attr("confirm-error");
        var method = $(this).attr("confirm-method");
        if(method !== 'post')
        {
            method = 'get';
        }
        if(!message || !url)
        {
            Notify.error('缺少属性confirm-message或confirm-url');
            return;
        }
        ClickUtil.confirm(message, function () {
            RequestUtil[method](url, {}, function () {
                if(success)
                {
                    Notify.success(success);
                }else{
                    Notify.success('请求成功');
                }
                if(typeof(listApp) !== 'undefined' && listApp.getData)
                {
                    listApp.getData();
                }
            },function () {
                if(error)
                {
                    Notify.error(error);
                }else{
                    Notify.error('请求失败');
                }
            });
        });
    });
});

var ClickUtil = {
    confirm: function(message, callback) {
        layer.confirm(message,{icon: 3, title: '提示信息'},function(index){
            callback();
            layer.close(index);
        });
    }
};

/**
 * 模态框里动态加载js,及执行回调
 * @param sUrl
 * @param callback
 * @constructor
 */
function JavaScriptAdd(sUrl, callback) {
    var has = $('head script[src="' + sUrl + '"]');
    if(has.length > 0) //已经存在的不重复加载
    {
        callback();
        return;
    }
    var _script = document.createElement('script');
    _script.setAttribute('type', 'text/javascript');
    _script.setAttribute('src', sUrl);
    document.getElementsByTagName('head')[0].appendChild(_script);
    //判断旧版本里面的swfobject是否执行完毕,如果执行就调用之前的代码
    if(_script.readyState){ // IE
        _script.onreadystatechange = function(){
            if(_script.readyState == "loaded" || _script.readyState == "complete"){
                _script.onreadystatechange = null;
                callback();
            }
        };
    }else{ // FF, Chrome, Opera, ...
        _script.onload = function(){
            callback();
        };
    }
}


function postOpen(URL, PARAMTERS) {
    //创建form表单
    var temp_form = document.createElement("form");
    temp_form.action = URL;
    //如需打开新窗口，form的target属性要设置为'_blank'
    temp_form.target = "_blank";
    temp_form.method = "post";
    temp_form.style.display = "none";
    //添加参数
    for (var item in PARAMTERS) {
        var opt = document.createElement("textarea");
        opt.name = PARAMTERS[item].name;
        opt.value = PARAMTERS[item].value;
        temp_form.appendChild(opt);
    }
    document.body.appendChild(temp_form);
    //提交数据
    temp_form.submit();
}


toastr.options = {
    closeButton: false,
    debug: false,
    progressBar: true,
    positionClass: "toast-top-right",
    onclick: null,
    showDuration: "300",
    hideDuration: "1000",
    timeOut: "2000",
    extendedTimeOut: "1000",
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut"
};
var Notify = {
    success: function(message) {
        toastr.success(message)
    },
    error: function(message) {
        toastr.error(message)
    }
};

var RequestUtil = {
    post: function (url, data, successCallback, errorCallback, notifyError, showAll) {
        var that = this;
        axios.post(url, data).then(function (response) {
            that.returnResult(response, successCallback, errorCallback, showAll, notifyError)
        }).catch(function (error) {
            that.errorResult(errorCallback, error, notifyError);
        })
    },
    get: function (url, data, successCallback, errorCallback, notifyError, showAll) {
        var that = this;
        axios.get(url, data).then(function (response) {
            that.returnResult(response, successCallback, errorCallback, showAll, notifyError)
        }).catch(function (error) {
            that.errorResult(errorCallback, error, notifyError);
        })
    },
    getAxiosParams: function (data) {
        var params = new URLSearchParams();
        for(var k in data)
        {
            params.append(k, data[k]);
        }
        return params;
    },
    getVueParams: function (vue, fields) {
        var params = new URLSearchParams();
        for (var k in fields)
        {
            var field = fields[k];
            params.append(field, vue[field]);

        }
        return params;
    },
    returnResult: function (response, successCallback, errorCallback, showAll, notifyError) {
        if(response.status !== 200)
        {
            if(errorCallback)
            {
                errorCallback();
            }
            //是否自动处理错误提示
            if(notifyError !== false)
            {
                Notify.error('网络请求异常');
            }
            return;
        }
        if(!response.data.code)
        {
            if(errorCallback)
            {
                errorCallback();
            }
            if(notifyError !== false)
            {
                Notify.error('请求返回结果不正确');
            }
            return;
        }
        if(response.data.code !== 200)
        {
            if(notifyError !== false)
            {
                Notify.error(response.data.info);
            }
            if(errorCallback)
            {
                errorCallback();
            }
            return;
        }
        //成功返回,是否显示所有响应信息
        if(showAll === true)
        {
            successCallback(response);
        }else{
            successCallback(response.data);
        }
    },
    errorResult: function (errorCallback, error, notifyError){
        if(notifyError !== false)
        {
            Notify.error('网络请求异常');
        }
        if(errorCallback)
        {
            errorCallback(error);
        }
        console.log('错误信息：' + error)
    }
};


Vue.filter('repeat', function (value, num) {
    return (new Array(num + 1)).join(value);
});

Vue.filter('replace', function (value, oldText, newText) {
    return value.replace(oldText, newText);
});

Vue.filter('timestampToTime', function (timestamp, showSecond) {
    if(timestamp==null||timestamp==''||!timestamp || timestamp <= 0) return '';
    var date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
    Y = date.getFullYear() + '-';
    M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
    D = date.getDate() + ' ';
    h = date.getHours() + ':';
    m = date.getMinutes() + ':';
    s = date.getSeconds();
    if(showSecond)
    {
        return Y+M+D+h+m+s;
    }
    return Y+M+D;
});


Vue.filter('show_username', function (user, showRealname) {
    if(!user) return'';
    if(showRealname && user.realname && user.realname !== '')
    {
        return user.realname;
    }
    if(user.username && user.username !== '')
    {
        return user.username;
    }
    if(user.mobile && user.mobile !== '')
    {
        return user.mobile;
    }
    return '';
});




Vue.filter('strlen', function (value, start, end) {
    return value.substr(start, end);
});

Vue.filter('financeNumber',function(v){
    v = Number(v);
    return v.toFixed(2);
})


Vue.prototype.operate= function (row) {
    var html = '';
    if(!build_operate)
    {
        return html;
    }
    for (var k in build_operate)
    {
        var data = build_operate[k];
        if(typeof (row_oprate) === 'function')
        {
            html += row_oprate(row, data['name'], data['alink']) + ' ';
            continue;
        }
        html += data['alink'].replace('id_value', row.id) + ' ';
    }
    return html;
};



Vue.prototype.in_array= function (search,array) {
    for(var i in array){
        if(array[i] == search){
            return true;
        }
    }
    return false;
};

Vue.prototype.field_value_in_array= function (searchKey, searchValue, array) {
    for(var i in array){
        if(array[i][searchKey] &&  array[i][searchKey] == searchValue){
            return true;
        }
    }
    return false;
};

Vue.prototype.array_unset= function (search,array) {
    for (var k = 0; k < array.length; k++)
    {
        if(array[k] == search)
        {
            array.splice(k,1);
            k--;
        }

    }
};



Vue.component('input_img_upload', {
    data: function () {
        return {
            type: null,
            uuid: null,
            img: [],
            dom_id: 'input_img_upload',
            file: '',
            show_file: '',
            max_file_num: 1,
            uploadApi: componentUrl.ajaxUploadApi,
            deleteApi: componentUrl.deleteFileApi,
            imgInit: false
        }
    },
    template: ' <div v-if="uuid && type && imgInit"><div class="input-group">\n' +
    '                                                <input class="form-control"  type="text" v-model="show_file"><input  v-on:change="fileChange"  v-bind:id="dom_id" class="form-control hidden"  type="file" value="">\n' +
    '                                                <div class="input-group-addon no-border no-padding">\n' +
    '                                                  <span><button type="button" v-on:click="selectFile" id="fachoose-image" class="btn btn-primary fachoose" data-input-id="c-image" data-mimetype="image/*" data-multiple="false"><i class="fa fa-list"></i> 上传</button></span>\n' +
    '                                                </div>\n' +
    '                                                <span class="msg-box n-right" for="c-image"></span>\n' +
    '                                            </div>\n' +
    '                                            <ul   class="row list-inline plupload-preview" id="p-image">\n' +
    '                                                <li class="col-xs-12" v-for="row in img">\n' +
    '                                                    <a target="_blank" v-bind:href="row.url" class="thumbnail"><img v-bind:src="row.url" class="img-responsive"></a><a href="javascript:;" v-on:click="deleteImg(row.id)" class="btn btn-danger btn-xs btn-trash"><i class="fa fa-trash"></i></a>\n' +
    '                                                </li>\n' +
    '                                            </ul></div>',
    props:['id_name', 'parent_uuid','parent_type','max_file','upload_url'],
    methods:{
        selectFile: function () {
            $('#' + this.dom_id).click();
        },
        fileChange: function (event) {
            var value = event.target.value;
            if(value === '')
            {
                return;
            }
            if(this.img.length >= this.max_file_num)
            {
                Notify.error('当前位置图片数量已达上限')
                return;
            }
            this.file = event.target.files;
            this.show_file = value;
            var form = new FormData();
            form.append('file', this.file[0]);
            var that = this;
            RequestUtil.post(this.uploadApi + '?type=' + that.type + '&uuid=' + that.uuid, form, function (data) {
                that.show_file = '';
                RequestUtil.get(componentUrl.getFilesUrl+'?uuid='+that.uuid+'&type='+that.type, {}, function (d) {
                    if(d.data)
                    {
                        that.img = d.data;
                    }
                },function () {
                    Notify.error('刷新图片信息失败');
                })
            },function () {
                that.show_file = '';
                Notify.error('上传图片失败');
            });
        },
        deleteImg: function (id) {
            var that = this;
            ClickUtil.confirm('确定要删除这张图片吗', function () {
                RequestUtil.post(that.deleteApi + '?id=' + id, {}, function (data) {
                    RequestUtil.get(componentUrl.getFilesUrl+'?uuid='+that.uuid+'&type='+that.type, {}, function (d) {
                        if(d.data)
                        {
                            that.img = d.data;
                        }
                    },function () {
                        Notify.error('刷新图片信息失败');
                    })
                },function () {
                    Notify.error('删除图片失败');
                });
            });
        }
    },
    watch: {
    },
    mounted:function(){
        // uuid 和 type值必须带入
        if(!this.parent_type)
        {
            Notify.error('parent_type为空');
            console.log(this.parent_type);
            return;
        }
        if(!this.parent_uuid)
        {
            Notify.error('parent_uuid为空');
            console.log(this.parent_uuid);
            return;
        }
        this.uuid = this.parent_uuid;
        this.type = this.parent_type;
        if(this.id_name)
        {
            this.dom_id = this.id_name;
        }
        if(this.max_file)
        {
            this.max_file_num = this.max_file;
        }

        if(this.upload_url)
        {
            this.uploadApi = this.upload_url;
        }
        var that = this;
        RequestUtil.get(componentUrl.getFilesUrl+'?uuid='+this.uuid+'&type='+this.type, {}, function (d) {
            if(d.data)
            {
                that.img = d.data;
                that.imgInit = true;
            }
        },function () {
            Notify.error('获取图片信息失败');
        })
    }
});



