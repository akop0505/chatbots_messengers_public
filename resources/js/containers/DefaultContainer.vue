<template>
  <div class="app">
    <AppHeader fixed>
      <SidebarToggler class="d-lg-none" display="md" mobile />
      <b-link class="navbar-brand" to="#">
        <img class="navbar-brand-full" src="../img/brand/logo.svg" width="89" height="25" alt="CoreUI Logo">
        <img class="navbar-brand-minimized" src="../img/brand/sygnet.svg" width="30" height="30" alt="CoreUI Logo">
      </b-link>
      <SidebarToggler class="d-md-down-none" display="lg" />
      <!--<b-navbar-nav class="d-md-down-none">
        <b-nav-item class="px-3" to="/dashboard">Dashboard</b-nav-item>
        <b-nav-item class="px-3" to="/data" exact>Users</b-nav-item>
        <b-nav-item class="px-3">Settings</b-nav-item>
      </b-navbar-nav>-->
      <b-navbar-nav class="ml-auto">
        <b-nav-item class="d-md-down-none">
          <i class="icon-bell"></i>
          <b-badge pill variant="danger">5</b-badge>
        </b-nav-item>
        <b-nav-item class="d-md-down-none">
          <i class="icon-list"></i>
        </b-nav-item>
        <b-nav-item class="d-md-down-none">
          <i class="icon-location-pin"></i>
        </b-nav-item>
        <DefaultHeaderDropdownAccount/>
      </b-navbar-nav>
      <!--<AsideToggler class="d-none d-lg-block" />
      <AsideToggler class="d-lg-none" mobile />-->
    </AppHeader>
    <div class="app-body">
      <AppSidebar fixed>
        <SidebarHeader/>
        <SidebarForm/>
        <SidebarNav :navItems="nav"></SidebarNav>
        <SidebarFooter/>
        <SidebarMinimizer/>
      </AppSidebar>
      <main class="main">
        <Breadcrumb :list="list"/>
        <div class="container-fluid">
          <router-view></router-view>
        </div>
      </main>
      <AppAside fixed>
        <!--aside-->
        <DefaultAside/>
      </AppAside>
    </div>
    <TheFooter>
      <!--footer-->
      <div>
        <a href="https://coreui.io">CoreUI</a>
        <span class="ml-1">&copy; 2018 creativeLabs.</span>
      </div>
      <div class="ml-auto">
        <span class="mr-1">Powered by</span>
        <a href="https://coreui.io">CoreUI for Vue</a>
      </div>
    </TheFooter>
  </div>
</template>

<script>
    import nav from '../_nav';
    import {
        Aside as AppAside,
        AsideToggler,
        Breadcrumb,
        Footer as TheFooter,
        Header as AppHeader,
        Sidebar as AppSidebar,
        SidebarFooter,
        SidebarForm,
        SidebarHeader,
        SidebarMinimizer,
        SidebarNav,
        SidebarToggler
    } from '@coreui/vue';
    import DefaultAside from './DefaultAside';
    import DefaultHeaderDropdownAccount from './DefaultHeaderDropdownAccount';

    export default {
        name: 'full',
        components: {
            AsideToggler,
            AppHeader,
            AppSidebar,
            AppAside,
            TheFooter,
            Breadcrumb,
            DefaultAside,
            DefaultHeaderDropdownAccount,
            SidebarForm,
            SidebarFooter,
            SidebarToggler,
            SidebarHeader,
            SidebarNav,
            SidebarMinimizer
        },
        mounted(){
            this.loadSidebarNavs();
        },
        data() {
            return {
                nav: []
            }
        },
        computed: {
            name() {
                return this.$route.name
            },
            list() {
                return this.$route.matched.filter((route) => route.meta.label || "")
            }
        },
        methods: {
            loadSidebarNavs(){
                axios.get(configs.apiUrl + "/sidebar_navs_opened_access").then((response)=>{
                    if(response.data.success === true){
                        this.setNav(response.data.SidebarNavs);
                    }
                }).catch((e)=>{
                    //console.error(e);
                });
            },
            setNav(accessRoles){
                let role = this.$user.get().role;
                if (role === 'admin'){
                    this.nav = nav.items;
                    return;
                }
                let navList = [];
                try {
                    for(let value of nav.items){
                        if(value.children !== undefined && value.children.length > 0){
                            let filteredChildren = [];
                            for(let child of value.children){
                                if(accessRoles[child.routeName] !== undefined && accessRoles[child.routeName].includes(role)){
                                    filteredChildren.push(_.clone(child, true));
                                }
                            }
                            if(filteredChildren.length) {
                                let item = _.clone(value, true);
                                item.children = filteredChildren;
                                navList.push(_.clone(item, true));
                            }
                        }else{
                            if(value.routeName !== undefined &&  accessRoles[value.routeName] !== undefined ) {
                                if(accessRoles[value.routeName].includes(role)) {
                                    navList.push(_.clone(value, true));
                                }
                            }
                        }
                    }
                }
                catch (e) {
                    //console.log(e);
                }
                this.nav = navList;
            }
        }
    }
</script>
