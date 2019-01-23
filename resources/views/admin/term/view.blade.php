@extends('admin.layout.app')

@section('pageTitle', 'All Term')

@section('content')

<div id="main-wrapper">
    @include('admin.layout.header')
    @include('admin.layout.sidebar')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Data Term</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Data</a></li>
                    <li class="breadcrumb-item active">Data Term</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Data Term</h4>
                            <div class="pt-2">
                                <div class="d-flex">
                                    <a href="{{ route('process.term') }}" class="pl-2">
                                        <button type="button" class="btn waves-effect waves-light btn-success"><i class="fa fa-exchange"></i> Update Term</button>
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive m-t-10">
                                <table id="myTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nama Term</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $no = 1;    
                                        @endphp
                                        @foreach ($term as $item)
                                            <tr>
                                                <td>{{ $no }}</td>
                                                <td>{{ $item->nama_term }}</td>
                                            </tr>
                                            @php $no++; @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row m-t-40">
                                <div class="col b-r text-center">
                                    <h2 class="font-light">{{ $countTerm }}</h2>
                                    <h6>Total Term</h6>
                                </div>
                                <div class="col b-r text-center">
                                    <h2 class="font-light">{{ $countFilm }}</h2>
                                    <h6>Total Film</h6>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('admin.layout.footer')
    </div>
</div>

@section('add_js')

<script src="{{ asset('admin/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#myTable').DataTable({
            "searching": false
        });
    });
</script>
@endsection

@endsection