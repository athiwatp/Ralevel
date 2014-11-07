@extends('layout.master')

@section('content')
<section id="container" class="">
      <!--header start-->
      <aside>
          <div id="sidebar"  class="nav-collapse ">
              <!-- sidebar menu start-->
              <ul class="sidebar-menu" id="nav-accordion">
                  @foreach ($menus as $menu)
                  <li>
                      <a {{ (!isset($activeId)) ? '' : (($menu['id'] != $activeId) ? '' : ' class="active"') }} href="#">
                          <i class="icon-"></i>
                          <span>{{ strtoupper($menu['name']) }}</span>
                      </a>
                  </li>
                  @endforeach    
                  <li>
                    <a href="#modalAddDepartment" data-toggle="modal" class="btn btn-small btn-danger" style="color: white;">Tambah Departemen</a>
                  </li>            
              </ul>
              <!-- sidebar menu end-->
          </div>
      </aside>
      <!--sidebar end-->
      <!--main content start-->
      <section id="main-content">
      @yield('mainContent', 'yep')
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
  </section>
  @stop

  @section('content2')
  <h3>Hello, just  test</h3>
  @stop