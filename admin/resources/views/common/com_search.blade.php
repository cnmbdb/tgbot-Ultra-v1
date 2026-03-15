<div class="layui-btn-group com-search-btns">
    <button class="layui-btn layui-btn-sm btn-search" lay-submit lay-filter="{{isset($submit)?'submit':'go'}}" id="{{isset($submit)?'submit':'search'}}"><i class="fa fa-search"></i>&nbsp;{{isset($title)?$title:(isset($submit)?'确认':'搜索')}}</button>
    <button class="layui-btn layui-btn-sm btn-reset" type="reset" lay-submit lay-filter="{{isset($submit)?'reset':'search_reset'}}"><i class="fa fa-refresh"></i>&nbsp;重置</button>
</div>
