<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="images/logo-mini.png" type="image/ico"/>
        <title>SWIFT</title>

        <!-- Bootstrap -->
        <link href="../css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
        <!-- NProgress -->
        <link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
        <!-- iCheck -->
        <link href="../vendors/iCheck/skins/flat/green.css" rel="stylesheet">
        <!-- Datatables -->
        
        <link href="../vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
        <link href="../vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
        <link href="../vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet">
        <link href="../vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
        <link href="../vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet">

        <!-- Custom Theme Style -->
        <link href="../build/css/custom.min.css" rel="stylesheet">
    </head>

    <body class="nav-md"> 
        <div class="container body">
            <div class="main_container">
                <div class="col-md-3 left_col">
                    <div class="left_col scroll-view ">
                        <div class="navbar nav_title" style="border: 0; text-align:center; ">
                        <a href="" class="site_title"><img class="img-responsive" src="images/logo.png" alt="logo" style="width: 90%; height: 80%"/></a>
                        </div>
                        <div class="clearfix"></div>
                        @include('layout.menu_profile')
                        <br />
                        @include('layout.sidebar')
                    </div>
                </div>
                @include('layout.header')
                <div class="right_col" role="main">

                    <div class="clearfix"></div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 ">
                            <div class="x_panel">
                                <div class="x_title"> 
                                    <h2>List of files <small>SWIFT MT 950</small></h2>
                                    
                                    <ul class="nav navbar-right panel_toolbox">
                                    <a href="{{ route('list950') }}" class="btn btn-success ">Back</a>
                                    </ul>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="card-box table-responsive"> 
                                                <div class="col-md-12 mb-10">
                                                    <!--<ul class="nav nav-tabs tab-basic" role="tablist">
                                                        <li class="nav-item" style="height: 50px;">
                                                        <a class="nav-link active" id="mt950-tab" data-toggle="tab" href="#mt950" role="tab"
                                                            aria-controls="mt950" aria-selected="false">MT950</a>
                                                        </li>
                                                    </ul>
                                                    <br>-->
                                                    <div class="tab-content tab-content-basic">
                                                        <div class="tab-pane fade show active" id="mt950" role="tabpanel" aria-labelledby="profile-tab">
                                                            <table id="datatable" class="table table-striped table-bordered" style="width:100%">
                                                                <thead>
                                                                    <tr>
                                                                    <th>#</th>
                                                                    <th>Type</th>
                                                                    <th>Sender</th>
                                                                    <th>Transaction Reference Number</th>
                                                                    <th>Date</th>
                                                                    <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($test_file_MT950 as $key=>$con950)
                                                                    <tr>
                                                                        <td class="text-muted">{{$key+1}}</td>
                                                                        <td>MT{{$con950->type}}</td>
                                                                        <td>{{$con950->sender}}</td>
                                                                        <td>{{$con950->trans_ref}}</td>
                                                                        <td>{{$con950->date->format('d-m-Y')}}</td> 
                                                                        <td>
                                                                        <button type="button" class="btn btn btn-info btn-xs" data-toggle="modal" 
                                                                        data-target="#view3_{{$con950->id}}" ><i class="fa fa-eye"></i></button>

                                                                        <button type="button" class="btn btn btn-info btn-xs" data-toggle="modal" 
                                                                        data-target="#view_detail_{{$con950->id}}" ><i class="fa fa-bars"></i></button>

                                                                        <a type="button" class="btn btn btn-success btn-xs" href="{{ url('/pdf_mt950/'.$con950->id) }}" target="_blank"><i class="fa fa-print" ></i></a></td>
                                                                    </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>

                                                            @foreach($test_file_MT950 as $tf950)
                                                                <!-- form modal -->
                                                                <div class="modal fade" id="view_detail_{{$tf950->id}}" tabindex="-1" 
                                                                    role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
                                                                    <div class="modal-dialog modal-xl" role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="ModalLabel">{{__('Détails')}}</h5>
                                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <div class="row">
                                                                                    <div class="col-6 text-left">
                                                                                        <fieldset class="scheduler-border">
                                                                                            <img class="img-responsive" src="images/logo.png" alt="logo" style="width: 70%"/>
                                                                                            <p></p>
                                                                                            <span class="font-weight-bold">Phone: (+216) 70131700</span><br>
                                                                                            <span class="font-weight-bold">Address: Avenue Maitre Mohamed Beji Caied Essebsi - Centre Urbain Nord - 1082 Tunis</span>
                                                                                        </fieldset>
                                                                                    </div>
                                                                                </div>
                                                                                <br>
                                                                                <br>
                                                                                <div class="row">
                                                                                    <fieldset class="form-group border p-3 py-0 w-100" >
                                                                                        <legend class="w-auto px-2 font-weight-bold">Statement Line</legend>
                                                    
                                                                                        <table class="table table-striped table-bordered" style="width:100%">
                                                                                            <thead>
                                                                                                <tr>
                                                                                                    <th>#</th>
                                                                                                    <th>Tag</th>
                                                                                                    <th>Value</th>
                                                                                                    <th>Detail</th>
                                                                                                </tr>
                                                                                            </thead>
                                                                                            <tbody>
                                                                                                @foreach($tf950->tag_61 as $key=>$tag_61)
                                                                                                <tr>
                                                                                                    <td class="text-muted">{{$key+1}}</td>
                                                                                                    <td>{{$tag_61->entr_statement_new}}</td>
                                                                                                    <td>{{$tag_61->value_statement_new}}</td>
                                                                                                    <td>{{$tag_61->code_statement}}</td>
                                                                                                </tr>
                                                                                                @endforeach
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </fieldset>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- /form modal -->
                                                            @endforeach

                                                            @foreach($test_file_MT950 as $tf950)
                                                                <!-- form modal -->
                                                                <div class="modal fade" id="view3_{{$tf950->id}}" tabindex="-1" 
                                                                    role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
                                                                    <div class="modal-dialog modal-lg " role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="ModalLabel">{{__('Détails')}}</h5>
                                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <div class="row">
                                                                                    <div class="col-6 text-left">
                                                                                        <fieldset class="scheduler-border">
                                                                                            <img class="img-responsive" src="images/logo.png" alt="logo" style="width: 70%"/>
                                                                                            <p></p>
                                                                                            <span class="font-weight-bold">Phone: (+216) 70131700</span><br>
                                                                                            <span class="font-weight-bold">Address: Avenue Maitre Mohamed Beji Caied Essebsi - Centre Urbain Nord - 1082 Tunis</span>
                                                                                        </fieldset>
                                                                                    </div>
                                                                                </div>
                                                                                <br>
                                                                                <br>
                                                                                <div class="row">
                                                                                    <fieldset class="form-group border p-3 py-0 w-100" >
                                                                                        <legend class="w-auto px-2 font-weight-bold">Message Text</legend>
                                                                                        <table>
                                                                                        <td style="color: black">
                                                                                            {{!! nl2br(htmlspecialchars($tf950->rows, ENT_NOQUOTES)) !!}}
                                                                                        </td>
                                                                                        </table>
                                                                                    </fieldset>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- /form modal -->
                                                            @endforeach


                                                            
                                                        </div>            
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>   
                @include('layout.footer')
            </div>
        </div>
    
        <!-- jQuery -->
        <script src="../vendors/jquery/dist/jquery.min.js"></script>
        <!-- Bootstrap -->
        <script src="../vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <!-- FastClick -->
        <script src="../vendors/fastclick/lib/fastclick.js"></script>
        <!-- NProgress -->
        <script src="../vendors/nprogress/nprogress.js"></script>
        <!-- iCheck -->
        <script src="../vendors/iCheck/icheck.min.js"></script>
        <!-- Datatables -->
        <script src="../vendors/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="../vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
        <script src="../vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
        <script src="../vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
        <script src="../vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
        <script src="../vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
        <script src="../vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
        <script src="../vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
        <script src="../vendors/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
        <script src="../vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
        <script src="../vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
        <script src="../vendors/datatables.net-scroller/js/dataTables.scroller.min.js"></script>
        <script src="../vendors/jszip/dist/jszip.min.js"></script>
        <script src="../vendors/pdfmake/build/pdfmake.min.js"></script>
        <script src="../vendors/pdfmake/build/vfs_fonts.js"></script>

        <!-- Custom Theme Scripts -->
        <script src="../build/js/custom.min.js"></script>

        <script>
            $(function () {
                //Date picker
                $('#DateDebut').datetimepicker({
                    format: 'L'
                });

                $('#DateFin').datetimepicker({
                    format: 'L'
                });
            });
        </script>

    </body>
</html>


