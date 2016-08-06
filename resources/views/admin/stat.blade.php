@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-7">
                <div class="row">
                    <div class="panel panel-success">
                        <div class="panel-heading">Pages</div>
                        <div class="panel-body">
                            <table id="pg_table" class="table">
                                <thead>
                                <tr>
                                    <th>URL</th>
                                    <th>Hit</th>
                                    <th>Unique by IP</th>
                                    <th>Unique by cookie</th>
                                </tr>
                                </thead>
                                <tbody>
                                <td><a href="{{url('admin/stat')}}">Site Total</a></td>
                                <td>{{$r->hget($pg_key.':0',\App\Jobs\AddStat::HIT_PREF)}}</td>
                                <td>{{$r->hget($pg_key.':0',\App\Jobs\AddStat::UQ_IP_PREF)}}</td>
                                <td>{{$r->hget($pg_key.':0',\App\Jobs\AddStat::UQ_COOKIE_PREF)}}</td>
                                @foreach($pages as $pg=>$pg_id)
                                    <tr>
                                        <td><a href="{{url('admin/stat',['pg_id'=>$pg_id])}}">{{$pg}}</a></td>
                                        <td>{{$r->hget($pg_key.':'.$pg_id,\App\Jobs\AddStat::HIT_PREF)}}</td>
                                        <td>{{$r->hget($pg_key.':'.$pg_id,\App\Jobs\AddStat::UQ_IP_PREF)}}</td>
                                        <td>{{$r->hget($pg_key.':'.$pg_id,\App\Jobs\AddStat::UQ_COOKIE_PREF)}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="panel panel-primary">
                    <div class="panel-heading">Browsers</div>
                    <div class="panel-body">
                        <table id="br_table" class="table">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Hit</th>
                                <th>Unique by IP</th>
                                <th>Unique by cookie</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($browsers as $br=>$br_id)
                                <tr>
                                    <td>{{$br}}</td>
                                    <td>{{$r->hget($br_key.':'.$br_id,\App\Jobs\AddStat::HIT_PREF)}}</td>
                                    <td>{{$r->hget($br_key.':'.$br_id,\App\Jobs\AddStat::UQ_IP_PREF)}}</td>
                                    <td>{{$r->hget($br_key.':'.$br_id,\App\Jobs\AddStat::UQ_COOKIE_PREF)}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel panel-warning">
                    <div class="panel-heading">Operating systems</div>
                    <div class="panel-body">
                        <table id="os_table" class="table">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Hit</th>
                                <th>Unique by IP</th>
                                <th>Unique by cookie</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($oss as $os=>$os_id)
                                <tr>
                                    <td>{{$os}}</td>
                                    <td>{{$r->hget($os_key.':'.$os_id,\App\Jobs\AddStat::HIT_PREF)}}</td>
                                    <td>{{$r->hget($os_key.':'.$os_id,\App\Jobs\AddStat::UQ_IP_PREF)}}</td>
                                    <td>{{$r->hget($os_key.':'.$os_id,\App\Jobs\AddStat::UQ_COOKIE_PREF)}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Countries</div>
                    <div class="panel-body">
                        <table id="geo_table" class="table">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Hit</th>
                                <th>Unique by IP</th>
                                <th>Unique by cookie</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($geos as $geo=>$geo_id)
                                <tr>
                                    <td>{{$geo}}</td>
                                    <td>{{$r->hget($geo_key.':'.$geo_id,\App\Jobs\AddStat::HIT_PREF)}}</td>
                                    <td>{{$r->hget($geo_key.':'.$geo_id,\App\Jobs\AddStat::UQ_IP_PREF)}}</td>
                                    <td>{{$r->hget($geo_key.':'.$geo_id,\App\Jobs\AddStat::UQ_COOKIE_PREF)}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Referents</div>
                    <div class="panel-body">
                        <table id="ref_table" class="table">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Hit</th>
                                <th>Unique by IP</th>
                                <th>Unique by cookie</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($refs as $ref=>$ref_id)
                                <tr>
                                    <td>{{$ref}}</td>
                                    <td>{{$r->hget($ref_key.':'.$ref_id,\App\Jobs\AddStat::HIT_PREF)}}</td>
                                    <td>{{$r->hget($ref_key.':'.$ref_id,\App\Jobs\AddStat::UQ_IP_PREF)}}</td>
                                    <td>{{$r->hget($ref_key.':'.$ref_id,\App\Jobs\AddStat::UQ_COOKIE_PREF)}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
{{--@section('page_scripts')--}}
    {{--<script src="https://code.jquery.com/jquery-1.12.3.js"></script>--}}
    {{--<script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>--}}
    {{--<script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>--}}


    {{--<script type="javascript">--}}
        {{--$(document).ready(function() {--}}
            {{--var table_br =$('#br_table').DataTable({--}}
                {{--"data":[--}}
                        {{--['qwe',12,2,3]--}}
                {{--]--}}
            {{--});--}}
        {{--});--}}
    {{--</script>--}}
{{--@endsection--}}