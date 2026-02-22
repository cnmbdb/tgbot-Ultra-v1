<div class="layui-btn-group" style="margin: 10px 0 5px 10px">
    <button class="layui-btn soushou_button" lay-submit lay-filter="{{isset($submit)?'submit':'go'}}" id="{{isset($submit)?'submit':'search'}}"><i class="fa fa-search"></i><span class="mleft1">{{isset($title)?$title:(isset($submit)?'确认':'搜索')}}</span></button>
    <button class="layui-btn layui-btn-primary f_chongzhi" type="reset" lay-submit lay-filter="{{isset($submit)?'reset':'search_reset'}}"><i class="fa fa-refresh"><span class="mleft1">重置</span></i></button>
</div>
