<!--begin::Sidebar-->
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
    data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Logo-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <!--begin::Logo image-->
        <a href="{{ url('/') }}">
            <img alt="Logo" src="{{ getSetting()->logo }}"
                class="h-100px app-sidebar-logo-default theme-light-show" />
            <img alt="Logo" src="{{ getSetting()->logo }}"
                class="h-100px app-sidebar-logo-default theme-dark-show" />
            <img alt="Logo" src="{{ getSetting()->logo }}" class="h-70px app-sidebar-logo-minimize" />
        </a>
        <!--end::Logo image-->
        <!--begin::Sidebar toggle-->
        <!--begin::Minimized sidebar setup:
                                if (isset($_COOKIE["sidebar_minimize_state"]) && $_COOKIE["sidebar_minimize_state"] === "on") {
                                    1. "src/js/layout/sidebar.js" adds "sidebar_minimize_state" cookie value to save the sidebar minimize state.
                                    2. Set data-kt-app-sidebar-minimize="on" attribute for body tag.
                                    3. Set data-kt-toggle-state="active" attribute to the toggle element with "kt_app_sidebar_toggle" id.
                                    4. Add "active" class to to sidebar toggle element with "kt_app_sidebar_toggle" id.
                                }
                            -->
        <div id="kt_app_sidebar_toggle"
            class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
            data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
            data-kt-toggle-name="app-sidebar-minimize">
            <i class="ki-outline ki-black-left-line fs-3 rotate-180"></i>
        </div>
        <!--end::Sidebar toggle-->
    </div>
    <!--end::Logo-->
    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <!--begin::Scroll wrapper-->
            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true"
                data-kt-scroll-activate="true" data-kt-scroll-height="auto"
                data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px"
                data-kt-scroll-save-state="true">
                <!--begin::Menu-->
                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                    data-kt-menu="true" data-kt-menu-expand="false">
                    @foreach (getMenu() as $item)
                        @php
                            // Determine if current route matches any child route for accordion
                            $isAccordionActive =
                                isset($item['children']) &&
                                collect($item['children'])->contains(function ($sub) {
                                    return Route::currentRouteName() === $sub['route'];
                                });
                            // Determine if current route matches this item (for non-accordion)
                            $isItemActive =
                                !isset($item['children']) &&
                                isset($item['route']) &&
                                Route::currentRouteName() === $item['route'];
                        @endphp
                        @if (isset($item['children']))
                            <!--begin:Menu item-->
                            <div data-kt-menu-trigger="click"
                                class="menu-item menu-accordion{{ $isAccordionActive ? ' show' : '' }}">
                                <!--begin:Menu link-->
                                <span class="menu-link{{ $isAccordionActive ? ' active' : '' }}">
                                    <span class="menu-icon">
                                        <i class="ki-outline {{ $item['icon'] }} fs-2"></i>
                                    </span>
                                    <span class="menu-title">{{ $item['title'] }}</span>
                                    <span class="menu-arrow"></span>
                                </span>
                                <!--end:Menu link-->
                                <!--begin:Menu sub-->
                                <div class="menu-sub menu-sub-accordion">
                                    @foreach ($item['children'] as $sub)
                                        @php
                                            $isSubActive = Route::currentRouteName() === $sub['route'];
                                        @endphp
                                        <!--begin:Menu item-->
                                        <div class="menu-item">
                                            <!--begin:Menu link-->
                                            @if (Route::has($sub['route']))
                                                <a class="menu-link{{ $isSubActive ? ' active' : '' }}"
                                                    href="{{ route($sub['route']) }}">
                                                @else
                                                    <a class="menu-link" href="/">
                                            @endif
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">{{ $sub['title'] }}</span>
                                            </a>
                                            <!--end:Menu link-->
                                        </div>
                                        <!--end:Menu item-->
                                    @endforeach
                                </div>
                                <!--end:Menu sub-->
                            </div>
                            <!--end:Menu item-->
                        @else
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                @if (isset($item['route']) && Route::has($item['route']))
                                    <a class="menu-link{{ $isItemActive ? ' active' : '' }}"
                                        href="{{ route($item['route']) }}">
                                    @else
                                        <a class="menu-link" href="apps/calendar.html">
                                @endif
                                <span class="menu-icon">
                                    <i class="ki-outline {{ $item['icon'] }} fs-2"></i>
                                </span>
                                <span class="menu-title">{{ $item['title'] }}</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                        @endif
                    @endforeach
                </div>
                <!--end::Menu-->
            </div>
            <!--end::Scroll wrapper-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->
    <!--begin::Footer-->
    {{-- <div class="app-sidebar-footer flex-column-auto pt-2 pb-6 px-6" id="kt_app_sidebar_footer">
        <a href="https://preview.keenthemes.com/html/metronic/docs"
            class="btn btn-flex flex-center btn-custom btn-primary overflow-hidden text-nowrap px-0 h-40px w-100"
            data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss-="click" title="Keluar aplikasi">
            <span class="btn-label">Logout</span>
            <i class="ki-outline ki-document btn-icon fs-2 m-0"></i>
        </a>
    </div> --}}
    <!--end::Footer-->
</div>
<!--end::Sidebar-->
