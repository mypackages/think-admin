


/*var componentUrl = {
    fileConfigUrl:'<?php echo {:url("file/getConfig")};?>',
    fileUploadUrl:"{:url('file/upload')}",
    getFilesUrl:"{:url('file/getFiles')}",
    uuidUrl:"{:url('file/getUUID')}",
}*/

/*文件上传组件*/
Vue.component('file_upload', {
    template: '<input id="fileupload" name="files" type="file" multiple>',
    props:['parent_uuid','parent_type','uploaded_func','file_must','upload_url','status'],
    methods:{
        upload:function(){
            // 绑定上传成功后的回调函数
            var i = $('#fileupload').fileinput('getFilesCount');
            if(this.file_must!=null&&i<this.file_must){
                errorMessage('必须上传'+this.file_must+'个文件');
                return;
            }
            // 获取选中且未上传的文件的个数
            var files = $('#fileupload').fileinput("getFileStack");
            var j = files.length;
            if(j>0){
                var funcName = this.uploaded_func;
                var parent = this.$parent;
                if(funcName!=null){
                    $('#fileupload').on('filebatchuploadcomplete', function(event, data, previewId, index){
                        parent[funcName](event, data, previewId, index);
                    });
                }
                $("#fileupload").fileinput('upload');
            }else{
                if(this.uploaded_func!=null)
                    this.$parent[this.uploaded_func]()
            }
        },
    },
    mounted:function(){
        // uuid 和 type值必须带入
        var uuid = this.parent_uuid;
        var type = this.parent_type;
        console.log(type,uuid);
        if(uuid==null||type==null){
            errorMessage('parent_uuid或parent_type为空');
            $('#fileupload').remove();
            return;
        }

        // 根据类型值读取对应的文件配置
        var fileConfig;
        $.ajax({
            url:componentUrl.fileConfigUrl+'?type='+this.parent_type,
            type: "get",
            async: false,
            success: function (d) {
                if(d.code==200){
                    fileConfig = d.data;
                }
            }
        })
        if(fileConfig==null){
            errorMessage('文件参数未配置');
            $('#fileupload').remove();
            return;
        }

        // 上传路径
        var uploadUrl = this.upload_url;
        uploadUrl = uploadUrl!=null?uploadUrl:componentUrl.fileUploadUrl

        // 插件配置
        var options = {
            showUpload:false,
            showRemove:false,
            uploadUrl: uploadUrl,
            uploadExtraData:{uuid:uuid,type:type},
            language: 'zh',
            allowedFileExtensions: fileConfig.ext,
            maxFileCount: fileConfig.count,
            maxFileSize:fileConfig.size,
            browseOnZoneClick:true,
            validateInitialCount: true,
            initialPreviewAsData: true,
            overwriteInitial: false,
            dropZoneEnabled: false,
        }

        // 获取原有文件数组
        var initPreviews = null;
        $.ajax({
            url:componentUrl.getFilesUrl+'?uuid='+uuid,
            type: "get",
            async: false,
            success: function (d) {
                if(d.code==200){
                    initPreviews = d.data;
                }
            }
        })

        // 如果上传了文件，展示文件样式
        if(initPreviews!=null){
            var previews = [];
            var configs = [];
            var downloadUrl;
            var caption;
            var type;
            var fileType;
            for(var i in initPreviews){
                downloadUrl = initPreviews[i].url;
                caption = initPreviews[i].name;
                fileType = initPreviews[i].fileType;
                type = initPreviews[i].type;
                previews.push(downloadUrl);
                configs.push({
                    caption: caption,
                    downloadUrl: downloadUrl,
                    key: i,
                    type:type,
                    fileType:fileType,
                    url:initPreviews[i].deleteUrl,
                    size:initPreviews[i].size
                });
            }
            options.initialPreview = previews;
            options.initialPreviewConfig = configs;
        }

        // 控件初始化
        $("#fileupload").fileinput(options);
        if(this.status)
            $("#fileupload").fileinput('disable');
        $('#fileupload').on('filebeforedelete', function(event) {
            return new Promise(function(resolve, reject) {
                $.confirm({
                    title: '警告!',
                    content: '删除的数据不可恢复，确定吗?',
                    type: 'red',
                    buttons: {
                        '是，确定': {
                            btnClass: 'btn-primary text-white',
                            keys: ['enter'],
                            action: function(){
                                resolve();
                            }
                        },
                        '不，再想想': function(){
                        }
                    }
                });
            });
        });
    }
})

/*表格组件*/
Vue.component('table_service',{
    template:'<span></span>',
    methods:{
        loading:function(){
            $('#myModal').modal('show');
        },
        disLoading:function(){
            $('#myModal').modal('hide');
        }
    }
})

/*树组件*/
Vue.component('tree_service',{
    template:'<span></span>',
    methods:{
        // 初始化树
        init:function(treeId,url,onClick,onAsyncSuccess,otherParam,setFontCss,nodes,checkOpts){
            var setting = {
                view: {
                    showLine: true
                },
                data: {
                    simpleData: {
                        enable: true
                    }
                }
            };
            if(checkOpts!=null)
                setting.check = checkOpts;
            if(setFontCss != null)
                setting.view.fontCss = setFontCss;
            setting.callback = {};
            if(nodes==null){
                setting.async = {
                    enable:true,
                    url:url,
                    type:'post'
                };
                if(otherParam!=null)
                    setting.async.otherParam = otherParam;

                if(onAsyncSuccess!=null)
                    setting.callback.onAsyncSuccess = onAsyncSuccess;

            }
            if(onClick!=null)
                setting.callback.onClick = onClick;
            $.fn.zTree.init($('#'+treeId), setting,nodes);
        },
        // 异步刷新树
        refreshTree: function(treeId){
            var treeObj = $.fn.zTree.getZTreeObj(treeId);
            treeObj.reAsyncChildNodes(null, "refresh");
        },
        // 获取选中的树节点
        getCheckedNodeIds: function(treeId){
            var treeObj = $.fn.zTree.getZTreeObj(treeId);
            var filter = function(node){
                return node.checked==true;
            }
            var checkedNodes = treeObj.getNodesByFilter(filter);
            var nodeIds = [];
            for(var i in checkedNodes){
                nodeIds.push(checkedNodes[i].id);
            }
            if(nodeIds.length > 0){
                return nodeIds.join(',');
            }
            return null;
        },
        // 取消树节点的选中状态
        cancelSelectedNode:function(treeId){
            var treeObj = $.fn.zTree.getZTreeObj(treeId);
            treeObj.cancelSelectedNode();
        },
        // 获取选中的节点
        getSelectNodes:function(treeId){
            var treeObj = $.fn.zTree.getZTreeObj(treeId);
            var nodes = treeObj.getSelectedNodes();
            return nodes;
        },
        getAllNodes:function(treeId){
            var treeObj = $.fn.zTree.getZTreeObj(treeId);
            var nodes = treeObj.getNodes();
            nodes = treeObj.transformToArray(nodes);
            return nodes;
        },
        getNodesByFilter:function(treeId,filter){
            var treeObj = $.fn.zTree.getZTreeObj(treeId);
            var nodes = treeObj.getNodesByFilter(filter);
            return nodes;
        },
        getNodesByParams:function(treeId,field,value){
            var treeObj = $.fn.zTree.getZTreeObj(treeId);
            var node = treeObj.getNodeByParam(field, value, null);
            return node;
        },
        checkNodes:function(treeId,ids){
            var treeObj = $.fn.zTree.getZTreeObj(treeId);
            var nodes = this.getNodesByFilter(treeId, function(node){
                if($.inArray(node.id,ids)!=-1)
                    return node;
            });
            $.each(nodes,function(i,v){
                treeObj.checkNode(v, true, false);
            })
        }
    },
    mounted:function(){

    },
})

/*字符组件*/
Vue.component('string_service',{
    template:'<span></span>',
    methods:{
        replaceSpace: function(str){
            if(str==null){
                str='';
            }else{
                str+='';
            }
            return str.replace(/(^\s+)|(\s+$)/g,"");
        },
        getUUID: function(){
            $.ajax({
                url:componentUrl.uuidUrl,
                type: "get",
                async: false,
                success: function (d) {
                    val = d.data;
                }
            })
            return val;
        }
    }
})


/*树组件*/
Vue.component('page_service',{
    template:'<div class="row" style="text-align: center;">\n' +
    '           <div id="pagination"></div>\n' +
    '         </div>',
    methods:{
        renderPage:function(curr,count,limit,jumpFunc,pageId){
            if(pageId==null)
                pageId = 'pagination';
            layPage.render({
                curr:curr,
                elem: pageId,
                count: count,
                limit:limit,
                layout: ['count', 'prev', 'page', 'next', 'skip'],
                jump: jumpFunc
            });
        }
    }
})

Vue.component('confirm_service',{
    template:'<span></span>',
    methods:{
        deleteConfirm:function(confirm,cancel){
            return new Promise(function(resolve, reject) {
                $.confirm({
                    title: '警告!',
                    content: '数据不可恢复，确定吗?',
                    type: 'red',
                    buttons: {
                        '是，确定': {
                            btnClass: 'btn-primary text-white',
                            keys: ['enter'],
                            action: confirm
                        },
                        '不，再想想': {
                            action:cancel
                        }
                    }
                });
            });
        },
        /*设置tooltips展示为手动触发*/
        setToolTipManually:function(){
            $('[data-toggle="tooltip"]').tooltip({trigger:'manual'});
        },
        /*模态框关闭时，模态框内所有的tooltips隐藏*/
        modalToolTipConfig:function(modal,check,trigger){
            $('#'+modal).on('hide.bs.modal',function(){
                for(var i in check){
                    $('#'+check[i]).tooltip('hide');
                }
            })
        }
    }
})

Vue.component('rating-service',{
    template:'<input id="rating" name="input-name" type="number" class="rating" data-size="xs">',
    methods:{
        setRating:function(int){
            $('#rating').rating('update',int);
        },
        reset:function(){
            $('#rating').rating('reset');
        },
        init:function(onChange,onClear,step,starCaptions,starCaptionClasses,disabled){
            if(disabled==null)
                disabled = false;
            step = step==null?1:step;
            starCaptions = starCaptions==null?{1: '不重要', 2: '一般', 3: '重要', 4: '比较重要', 5: '非常重要'}:starCaptions;
            starCaptionClasses = starCaptionClasses==null?{1: 'label label-default', 2: 'label label-info', 3: 'label label-primary', 4: 'label label-warning', 5: 'label label-danger'}: starCaptionClasses;
            $("#rating").rating({
                language:'zh',
                rtl:false,
                step: step,
                starCaptions: starCaptions,
                starCaptionClasses: starCaptionClasses,
                disabled:disabled,
            });
            if(onChange!=null){
                $("#rating").on("rating:change", onChange);
            }
            if(onClear!=null){
                $("#rating").on("rating:clear", onClear);
            }
        },
        refresh:function(opts){
            $('#rating').rating('refresh',opts);
        }
    }
})

Vue.component('ckeditor-srv',{
    template:'<span></span>',
    methods:{
        getData:function(id){
            return CKEDITOR.instances[id].getData();
        },
        setData:function(id,d){
            CKEDITOR.instances[id].setData(d);
        },
        init:function(id,uuid,type,height,uploadUrl){
            var styles = {name:"styles",groups:['insert','styles' ]};
            if(id==null||type==null){
                console.log('id或type参数为空');
                styles.groups.shift();
            }
            var ckInstance = CKEDITOR.instances[id];
            if(ckInstance!=null){
                ckInstance.destroy();
            };
            if(uploadUrl==null){
                uploadUrl = componentUrl.ckUpload;
            }
            uploadUrl +='?type='+type;
            if(uuid!=null)
                uploadUrl += '&uuid='+uuid;

            if(height==null)
                height = 300;
            CKEDITOR.replace(id,{
                filebrowserUploadUrl: uploadUrl,
                height:height,
                color:'red',
                toolbarGroups: [
                    {name: 'clipboard', groups: ['basicstyles', 'clipboard', 'undo','links',] },
                    styles,
                ],
                removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar'
            });
        }
    }
})

Vue.component('jqSelect2',{
    template:'<select id="jqSelect2" class="form-control select2 select2-hidden-accessible" ' +
    '               multiple="" data-placeholder="请选择考试" style="width: 100%;" tabindex="-1"  aria-hidden="true">' +
    '        </select>',
    props:['data','callBackName','select2Url'],
    methods:{
        init:function(select2Url,data,initData){
            if(select2Url==null&&data==null){
                errorMessage('jqSelect2无数据源');
                return;
            }
            var options = {
                matcher:matchCustom,
                disabled:status,
            };
            if(select2Url!=null){
                options.ajax = {
                    url: select2Url,
                    dataType: 'json',
                };
            }else{
                options.data = this.data;
            }
            $("#jqSelect2").select2(options);
            if(initData!=null)
                this.setValue(initData);
        },
        setValue:function(v){
            $("#jqSelect2").val(v).trigger("change");
        },
        getValue:function(){
            return $('#jqSelect2').select2("val");
        },
    },
    mounted:function(){
        this.init(this.select2Url,this.data,this.initData);
    },
})


Vue.component('select_menu',{
    template:
    '<div class="col-md-2" style="width:200px;">' +
    '   <input class="form-control" placeholder="选择考试" id="examSel" type="text" @focus="showMenu()" autocomplete="off" style="width:180px;"/>' +
    '   <div id="menuContent" class="menuContent">' +
    '       <ul id="tree_menu" class="ztree"></ul>' +
    '   </div>' +
    '</div>',
    props:['link','func','data'],
    methods:{
        init:function(obj){
            var setting = {
                view: {
                    showLine: true
                },
                data: {
                    simpleData: {
                        enable: true
                    }
                },
                async:{
                    type:'post',
                    enable:true,
                    url:obj.link,
                    autoParam:obj.data,
                },
                callback: {
                }
            };
            var parent = this.$parent;
            setting.callback.onClick = function(event, treeId, treeNode){
                if(obj.func!=null){
                    if(!treeNode.isParent){
                        $('#examSel').val(treeNode.name);
                        parent.params.exam_id = treeNode.id;
                        parent[obj.func]();
                    }else{
                        obj.unSelected();
                        layer.msg('请选择具体的考试');
                    }
                }

            };
            $.fn.zTree.init($('#tree_menu'), setting);
        },
        hideMenu:function(){
            $("#menuContent").fadeOut('fast');
        },
        showMenu:function(){
            $('#menuContent').slideDown('fast');
        },
        unSelected:function(){
            var treeObj = $.fn.zTree.getZTreeObj('tree_menu');
            treeObj.cancelSelectedNode();
            $("#examSel").val('');
        },
    },
    mounted:function(){
        var obj = this;
        obj.init(obj);
        $('body').bind('click',function(event){
            var id = event.target.id;
            if(id==null||(id!=null&&id != "examSel"&&id.indexOf('switch')==-1)){
                obj.hideMenu();
            }
        })
        $('#tree_menu').css('margin-top','0px');
    },
})


Vue.component('subject_menu',{
    template:
    '<div class="col-md-2" style="width:200px;">' +
    '   <input class="form-control" placeholder="选择考试或科目" id="sel" type="text" @focus="showMenu()" autocomplete="off" style="width:180px;"/>' +
    '   <div id="menuContent" class="menuContent">' +
    '       <ul id="tree_menu" class="ztree"></ul>' +
    '   </div>' +
    '</div>',
    props:['link','func','data'],
    methods:{
        init:function(obj){
            var setting = {
                view: {
                    showLine: true
                },
                data: {
                    simpleData: {
                        enable: true
                    }
                },
                async:{
                    type:'post',
                    enable:true,
                    url:obj.link,
                    autoParam:obj.data,
                },
                callback: {
                }
            };
            var parent = this.$parent;
            setting.callback.onClick = function(event, treeId, treeNode){
                if(treeNode.type=='subject'){
                    parent.params.subject_id = treeNode.realId;
                }else{
                    if(treeNode.level==0){
                        layer.msg('请选择具体的考试');
                        return;
                    }
                    parent.params.exam_id = treeNode.realId;
                    //obj.unSelected();
                    //layer.msg('请选择具体的科目');
                }
                $('#sel').val(treeNode.name);
                if(obj.func!=null){
                    parent[obj.func]();
                }


            };
            $.fn.zTree.init($('#tree_menu'), setting);
        },
        hideMenu:function(){
            $("#menuContent").fadeOut('fast');
        },
        showMenu:function(){
            $('#menuContent').slideDown('fast');
        },
        unSelected:function(){
            var treeObj = $.fn.zTree.getZTreeObj('tree_menu');
            treeObj.cancelSelectedNode();
            $("#sel").val('');
        },
    },
    mounted:function(){
        var obj = this;
        obj.init(obj);
        $('body').bind('click',function(event){
            var id = event.target.id;
            if(id==null||(id!=null&&id != "sel"&&id.indexOf('switch')==-1)){
                obj.hideMenu();
            }
        })
        $('#tree_menu').css('margin-top','0px');
    },
})


Vue.component('date_range',{
    data:function(){
        return {
            p:{},
        }
    },
    template:
    '<input type="text" class="form-control" id="dateRange"  placeholder="选择时间" >',
    methods:{
        init:function(){
            this.cleanTxt();
            var max = this.p.max;
            if(max==null||max==''){
                var date = new Date();
                max = date .getFullYear()+'-'+(date .getMonth()+1)+'-'+date .getDate();
            }

            var dateFormat = this.p.dateFormat;
            if(dateFormat==null||dateFormat=='')
                dateFormat = 'yyyy-MM-dd';

            var parent = this.p;
            var options = {
                elem: '#dateRange',
                range: true,
                format: dateFormat,
                max: max,
                change:function(value, date, endDate){
                    var between = value.split(' ');
                    var start = between[0]+' 00:00:00';
                    var end =  between[2]+' 23:59:59';
                    if(parent.isTimestamp){
                        start = new Date(start).getTime()/1000;
                        end = new Date(end).getTime()/1000;
                    }
                    parent.beginTime = start;
                    parent.endTime = end;
                }
            };
            if(this.p.initRangeValue!=null)
                options.value = this.p.initRangeValue;
            laydate.render(options);
        },
        cleanTxt:function(){
            this.p.beginTime = null;
            this.p.endTime = null;
            $("#dateRange").val('');
        },
    },
    mounted:function(){
        this.p = this.$parent;
        this.init();
    },
})

/*下拉树组件*/
Vue.component('select_tree',{
    data:function(){
        return {
            p:{},
        }
    },
    template:   '<div class="btn-group tree-btn-group" style="width:100%;">' +
    '<input type="text" class="form-control tree-input" id="dropDownInput">' +
    '<div id="dropDownDiv" class="zree-input-select-div">' +
    '<ul id="dropDown" class="ztree"></ul>' +
    '</div>' +
    '</div>',
    methods:{
        // 初始化树
        init:function(url,onClick,onAsyncSuccess,otherParam,setFontCss){
            var setting = {
                view: {
                    showLine: true
                },
                data: {
                    simpleData: {
                        enable: true
                    }
                }
            };
            if(setFontCss != null)
                setting.view.fontCss = setFontCss;

            setting.async = {
                enable:true,
                url:url,
                type:'post'
            };
            if(otherParam!=null)
                setting.async.otherParam = otherParam;

            setting.callback = {};
            if(onClick!=null)
                setting.callback.onClick = onClick;

            if(onAsyncSuccess!=null)
                setting.callback.onAsyncSuccess = onAsyncSuccess;

            $.fn.zTree.init($('#dropDown'), setting);
        },
        // 异步刷新树
        refreshTree: function(treeId){
            var treeObj = $.fn.zTree.getZTreeObj(treeId);
            treeObj.reAsyncChildNodes(null, "refresh");
        },
        // 获取选中的树节点
        getCheckedNodeIds: function(){
            var treeObj = $.fn.zTree.getZTreeObj('dropDown');
            var filter = function(node){
                return node.checked==true;
            }
            var checkedNodes = treeObj.getNodesByFilter(filter);
            var nodeIds = [];
            for(var i in checkedNodes){
                nodeIds.push(checkedNodes[i].id);
            }
            if(nodeIds.length > 0){
                return nodeIds.join(',');
            }
            return null;
        },
        // 取消树节点的选中状态
        cancelSelectedNode:function(){
            var treeObj = $.fn.zTree.getZTreeObj('dropDown');
            treeObj.cancelSelectedNode();
            $('#selectMenu').parents('.tree-btn-group').find('input').val('');
        },
        // 获取选中的节点
        getSelectNodes:function(){
            var treeObj = $.fn.zTree.getZTreeObj('dropDown');
            var nodes = treeObj.getSelectedNodes();
            return nodes;
        },
        getAllNodes:function(){
            var treeObj = $.fn.zTree.getZTreeObj('dropDown');
            var nodes = treeObj.getNodes();
            nodes = treeObj.transformToArray(nodes);
            return nodes;
        },
        getNodesByFilter:function(filter){
            var treeObj = $.fn.zTree.getZTreeObj('dropDown');
            var nodes = treeObj.getNodesByFilter(filter);
            return nodes;
        },
        getNodesByParams:function(field,value){
            var treeObj = $.fn.zTree.getZTreeObj('dropDown');
            var node = treeObj.getNodeByParam(field, value, null);
            return node;
        },
    },
    mounted:function(){
        $('body').on('click',function(e){
            if($(e.target).parents('.tree-btn-group').length<1)
                $('.zree-input-select-div').hide();
        });
        $('#dropDownInput').on('click',function(e) {
            $('#dropDownDiv').show();
        });
    },
})


function dynamicAddTag(type,parent,attrs,func){
    var tag = document.createElement(type);
    for(var i in attrs){
        tag.setAttribute(attrs[i].name, attrs[i].val);
    }
    document.getElementsByTagName(parent)[0].appendChild(tag);
    if(func!=null)
        tag.onload = func;
}

function matchCustom(params, data) {
    if ($.trim(params.term) === '') {
        return data;
    }

    if (typeof data.text === 'undefined') {
        return null;
    }
    if(data.text.indexOf(params.term)!=-1)
        return data;
    return null;
}

function replaceSpace(str){
    if(str==null||str==undefined)
        return '';
    str+='';
    return str.replace(/\s+/g,'');
}


function deleteConfirm(tips,vm,funcName,title){
    if(replaceSpace(tips)=='')
        tips = '即将执行删除操作,您确定吗?';
    if(title==null)
        title = '删除确认';
    $.confirm({
        title: title,
        content: tips,
        type: 'red',
        icon: 'glyphicon glyphicon-question-sign',
        buttons: {
            ok: {
                text: '确认',
                btnClass: 'btn-success',
                action: function() {
                    vm[funcName]();
                }
            },
            cancel: {
                text: '取消',
                btnClass: 'btn-default'
            }
        }
    });
}






