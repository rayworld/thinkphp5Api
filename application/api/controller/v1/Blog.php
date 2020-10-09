<?php

namespace app\api\controller\v1;

class Blog extends common
{
    /**
     * 新增博客
     *
     * @return void [空]
     */
    public function insert()
    {
        //验证请求方法
        $this->validate_request('post');
        //获得请求参数
        $data = $this->params;
        //设置上传时间
        $data['create_time'] = time();
        //插入记录，返回ID
        $res = db('article')->insertGetId($data);
        if ($res) {
            $this->return_msg(200, '新建文章成功');
        } else {
            $this->return_msg(400, '新建文章失败');
        }
    }

    /**
     * 更新博客
     *
     * @return void [空]
     */
    public function update()
    {
        //验证请求方法
        $this->validate_request('put');
        //获得请求参数
        $data = $this->params;
        //更新记录
        $res = db('article')
            ->where('article_id', $data['article_id'])
            ->update($data);
        if ($res) {
            $this->return_msg(200, '更新文章成功');
        } else {
            $this->return_msg(400, '更新文章失败');
        }
    }

    /**
     * 得到一条博客详情
     *
     * @return void [空]
     */
    public function detail()
    {
        //验证请求方法
        $this->validate_request('get');
        //获得请求参数
        $data = $this->params;
        //设置记录过滤
        $where['is_del'] = 0;
        $where['article_id'] = $data['arcitle_id'];
        //链接user表，得到用户昵称
        $join = [['api_user u', 'a.author_id = u.user_id']];
        //设置需要显示的列
        $field = "a.article_id, a.title, a.content, a.comment, u.nick_name";
        //执行数据库查询
        $res = db('article')
            ->alias('a')
            ->where($where)
            ->join($join)
            ->field($field)
            ->find();
        if ($res) {
            $this->return_msg(200, '取得文章信息成功', $res);
        } else {
            $this->return_msg(400, '取得文章信息失败', $res);
        }
    }

    /**
     * 删除博客
     *
     * @return void [空]
     */
    public function delete()
    {
        //验证请求方法
        $this->validate_request('delete');
        //获得请求参数
        $data = $this->params;
        //软删除
        $res = db('article')
            ->where('article_id', $data['article_id'])
            ->setField('is_del', 1);
        if ($res) {
            $this->return_msg(200, '删除文章成功');
        } else {
            $this->return_msg(400, '删除文章失败');
        }
        //物理删除

    }

    /**
     * 得到博客列表
     *
     * @return void [空]
     */
    public function get_list()
    {
        //验证请求方法
        $this->validate_request('get');
        //获得请求参数
        $data = $this->params;
        //为分页参数设置默认值
        if (!isset($data['page'])) {
            $data['page'] = 10;
        }
        if (!isset($data['num'])) {
            $data['num'] = 1;
        }
        //过滤记录
        $where['is_del'] = 0;
        $where['author_id'] = $data['user_id'];
        //获得记录条数
        $record_count = db('article')
            ->where($where)
            ->page($data['num'], $data['page'])
            ->count();
        //无记录则终止分页查询
        if ($record_count == 0) {
            $this->return_msg(200, '未查询到相关数据');
        }
        //获取总页数
        $page_count = ceil($record_count / $data['page']);
        //链接user表，得到用户昵称
        $join = [['api_user u', 'a.author_id = u.user_id']];
        //设置需要显示的列
        $field = "a.article_id, a.title, a.content, a.comment, u.nick_name";
        //执行数据库查询
        $res = db('article')
            ->alias('a')
            ->where($where)
            ->join($join)
            ->field($field)
            ->page($data['num'], $data['page'])
            ->select();
        if ($res) {
            //返回分页参数和数据
            $return_data['page_size'] = $data['page'];
            $return_data['page_index'] = $data['num'];
            $return_data['record_count'] = $record_count;
            $return_data['page_count'] = $page_count;
            $return_data['data'] = $res;
            $this->return_msg(200, '取得文章列表成功', $return_data);
        } else {
            $this->return_msg(400, '取得文章列表失败');
        }
    }
}
