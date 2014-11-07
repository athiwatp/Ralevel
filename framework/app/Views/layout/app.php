@extends('layouts.master')

@section('content')
<section id="container" class="">
      <!--header start-->
      <header class="header white-bg">
          <div class="sidebar-toggle-box">
              <div data-original-title="Toggle Navigation" data-placement="right" class="icon-reorder tooltips"></div>
          </div>
          <!--logo start-->
          <a href="index.html" class="logo" >PP <span>Al Mubarok</span> - <small>SantriBook App</small></a>
          <!--logo end-->
          <div class="top-nav ">
              <ul class="nav pull-right top-menu">
                  <li>
                      <input type="text" class="form-control search" placeholder="Search">
                  </li>
                  <!-- user login dropdown start-->
                  <li class="dropdown">
                      <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                          <img alt="" src="img/avatar1_small.jpg">
                          <span class="username">{{ Auth::user()->fullname }}</span>
                          <b class="caret"></b>
                      </a>
                      <ul class="dropdown-menu extended logout">
                          <div class="log-arrow-up"></div>
                          <li><a href="#"><i class=" icon-suitcase"></i>Profile</a></li>
                          <li><a href="#"><i class="icon-cog"></i> Settings</a></li>
                          <li><a href="#"><i class="icon-bell-alt"></i> Notification</a></li>
                          <li><a href="{{ action('AuthController@getLogout') }}"><i class="icon-key"></i> Log Out</a></li>
                      </ul>
                  </li>
                  <!-- user login dropdown end -->
              </ul>
          </div>
      </header>
      <!--header end-->
      <!--sidebar start-->
      <aside>
          <div id="sidebar"  class="nav-collapse ">
              <!-- sidebar menu start-->
              <ul class="sidebar-menu" id="nav-accordion">
                  @foreach ($menus as $menu)
                  <li>
                      <a {{ (!isset($activeId)) ? '' : (($menu['id'] != $activeId) ? '' : ' class="active"') }} href="{{ route('departments.santris.index', [$menu['id']]) }}">
                          <i class="icon-"></i>
                          <span>{{ strtoupper($menu['name']) }}</span>
                      </a>
                  </li>
                  @endforeach    
                  <li>
                    <a href="{{ action('dashboard') }}#modalAddDepartment" data-toggle="modal" class="btn btn-small btn-danger" style="color: white;">Tambah Departemen</a>
                  </li>            
              </ul>
              <!-- sidebar menu end-->
          </div>
      </aside>
      <!--sidebar end-->
      <!--main content start-->
      <section id="main-content">
      @yield('mainContent')
      </section>
      <!--main content end-->
      <!--footer start-->
      <footer class="site-footer">
          <div class="text-center">
              2014 &copy; SantriBook by Mokhamad Rofiudin. Special for Al Mubarok Life School.
              <a href="#" class="go-top">
                  <i class="icon-angle-up"></i>
              </a>
          </div>
      </footer>
      <!--footer end-->
      <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="modalAddDepartment" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h4 class="modal-title">Tambah Departemen</h4>
            </div>
            <div class="modal-body">
                <div class="status alert alert-success" style="display: none"></div>
                <form class="form-inline" role="form" id="form-add-department" method="post" action="{{{ action('departments.store') }}}">
                    <div class="row">
                        <label class="sr-only" for="addDepartemen">Nama Departemen</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="addDepartemen" data-required name="namaDepartment" placeholder="Tambah Departemen" autofocus>
                        </div>
                        <div class="col-sm-3 text-left">
                            <button type="submit" class="btn btn-success hiret-submit">Simpan <i class="icon-spin icon-spinner hide icon-large"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
      </div>
    </div>
    <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="modalUploadPhoto" class="modal fade">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h4 class="modal-title">Set &amp; Upload Photo</h4>
            </div>
            <div class="modal-body">
              <section class="panel">
                  <div class="panel-body">
                      <p>
                          Pilih area foto yang akan di-upload
                      </p>
                      <br>
                      <div class="row">
                          <div class="col-md-8">
                              <img src="img/no-image.jpg" id="photoCropper" class="jcrop-img" alt="Jcrop Example"  />
                          </div>
                          <div class="col-md-4 text-right pull-right">
                              <div id="preview-pane">
                                  <div class="preview-container">
                                      <img src="img/no-image.jpg" class="jcrop-preview jcrop-img" alt="Preview"/>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="panel-body">
                    
                      <div class="text-center">
                        <div class="row">
                          <button class="btn btn-success" id="btnUpload" data-url="{{ action('photos.store') }}"><i class="icon-spinner icon-spin hide"></i>   Proses</button>
                        </div>
                      </div>
                  </div>
              </section>
          </div>
        </div>
      </div>
    </div>
  </section>
  @stop