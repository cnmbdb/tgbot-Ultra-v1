/**
 * Created by kiki on 2017/6/19.
 * 延迟加载
 */

(function () {
    var options = {
        loadTaskMaxNum: 5 //延迟加载同时读取接口最大进程数
    };

    var lazyWaitQueue = new Array(); //延迟加载等待的队列
    var lazyProcessList = new Array(); //延迟加载读取中的列表

    //当前加载器状态
    var lazyLoadStatus = {
        currentTaskLoadNum : 0, //当前读取接口的进程数
        waitQueueTaskNum : 0 //延迟加载队列等待的任务数
    };

    function addLazyTask(task) {
        lazyWaitQueue.push(task);
        lazyLoadStatus.waitQueueTaskNum++;

        lazyLoadProcess();
    }

    //读取的进程数是否达到最大值
    function isLoadBusy(){
        return lazyLoadStatus.currentTaskLoadNum < options.loadTaskMaxNum ? false : true;
    }

    //触发加载器去处理队列中的任务
    function lazyLoadProcess(){

        //当加载器状态有空闲并且等待队列中有任务则处理队列中的任务
        while(!isLoadBusy() && lazyLoadStatus.waitQueueTaskNum > 0){

            lazyLoadStatus.currentTaskLoadNum++;
            lazyLoadStatus.waitQueueTaskNum--;
            var task = lazyWaitQueue.shift();
            lazyProcessList.push(task);
            getLazyLoadTaskData(task);
        }

    }

    //加载任务完成，从读取中列表移除，然后触发加载器运行
    function lazyLoadTaskDone(task){

        for(var i = 0; i< lazyProcessList.length; i++){

            if(lazyProcessList[i].id == task.id){

                lazyProcessList.splice(i,1);
                lazyLoadStatus.currentTaskLoadNum--;
                break;
            }
        }

        lazyLoadProcess();
    }

    //读取加载的接口数据并渲染页面
    function getLazyLoadTaskData(task){

        function onsuccess(data) {
            lazyLoadTaskDone(task);

            var datax = eval("(" + data + ")");
            if(datax["status"]=="1"){

                task.render(datax['data']);
            }

        }
        function onerrors(data) {
            lazyLoadTaskDone(task);
        }
        jQuery.comm.sendmessage(task.url, task.packet, onsuccess, onerrors);
    }

    //API
    var api = {
        config: function (opts) {
            if(!opts) return options;
            for(var key in opts) {
                options[key] = opts[key];
            }
            return this;
        },
        addTask: function (id, url, packet, render) {

            var task = {
                id: id,
                url: url,
                packet: packet,
                render: render
            };

            addLazyTask(task);
            return this;
        },
        resetQueue: function () {
            lazyLoadStatus.currentTaskLoadNum = 0;
            lazyLoadStatus.waitQueueTaskNum = 0;
            lazyWaitQueue = new Array();
            lazyProcessList = new Array();
            return this;
        }
    };
    this.lazyload = api;
})();
