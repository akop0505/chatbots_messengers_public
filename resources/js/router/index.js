import Vue from 'vue'
import Router from 'vue-router'

import DefaultContainer from '../containers/DefaultContainer';
// Views
import Dashboard from '../views/Dashboard';
import Templates from '../views/control/Templates';
import TemplatesForm from '../views/control/TemplatesForm';
import SelfManagementMenu from '../views/control/SelfManagementMenu';
import SelfManagementMenuForm from '../views/control/SelfManagementMenuForm';
import Users from '../views/projectSettings/Users';
import UsersForm from '../views/projectSettings/UsersForm';
import Roles from '../views/projectSettings/Roles';
import RolesForm from '../views/projectSettings/RolesForm';
import Receivers from '../views/projectSettings/Receivers';
import ReceiversForm from '../views/projectSettings/ReceiversForm';
import Events from '../views/projectSettings/Events';
import EventsForm from '../views/projectSettings/EventsForm';
import Gateway from '../views/projectSettings/Gateway';
import GatewayForm from '../views/projectSettings/GatewayForm';
import Projects from '../views/aiSystem/Projects';
import ProjectsForm from '../views/aiSystem/ProjectSettings';
import SequencesList from '../views/sequences/SequencesList';
import SequenceForm from '../views/sequences/SequenceForm';
import LeadsList from '../views/sequences/LeadsList';
import LeadsForm from '../views/sequences/LeadsForm';
import SidebarNavSetting from '../views/projectSettings/SidebarNavSetting';
import SidebarNavSettingForm from '../views/projectSettings/SidebarNavSettingForm';
import Dialog from '../views/dialog/Dialog';

import Colors from '../views/theme/Colors';
import Typography from '../views/theme/Typography';
import Charts from '../views/Charts';
import Widgets from '../views/Widgets';
// Views - Components
import Cards from '../views/base/Cards';
import Forms from '../views/base/Forms';
import Switches from '../views/base/Switches';
import Tables from '../views/base/Tables';
import Tabs from '../views/base/Tabs';
import Breadcrumbs from '../views/base/Breadcrumbs';
import Carousels from '../views/base/Carousels';
import Collapses from '../views/base/Collapses';
import Jumbotrons from '../views/base/Jumbotrons';
import ListGroups from '../views/base/ListGroups';
import Navs from '../views/base/Navs';
import Navbars from '../views/base/Navbars';
import Paginations from '../views/base/Paginations';
import Popovers from '../views/base/Popovers';
import ProgressBars from '../views/base/ProgressBars';
import Tooltips from '../views/base/Tooltips';
// Views - Buttons
import StandardButtons from '../views/buttons/StandardButtons';
import ButtonGroups from '../views/buttons/ButtonGroups';
import Dropdowns from '../views/buttons/Dropdowns';
import BrandButtons from '../views/buttons/BrandButtons';
// Views - Icons
import Flags from '../views/icons/Flags';
import FontAwesome from '../views/icons/FontAwesome';
import SimpleLineIcons from '../views/icons/SimpleLineIcons';
import CoreUIIcons from '../views/icons/CoreUIIcons';
// Views - Notifications
import Alerts from '../views/notifications/Alerts';
import Badges from '../views/notifications/Badges';
import Modals from '../views/notifications/Modals';
// Views - Pages
import Page404 from '../views/pages/Page404';
import Page500 from '../views/pages/Page500';
import Login from '../views/pages/Login';
import Register from '../views/pages/Register';
import axios from 'axios';
// Users
// import Users from '../views/users/Users';

Vue.use(Router);
Vue.prototype.$accessRoles = {};

const ifNotAuthenticated = (to, from, next) => {
    //console.log("ifNotAuthenticated");
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('token');
    if (localStorage.getItem('token')) {
        checkRole(to, from, next);
        next();
        return;
    }
    next('/login');
};
const  onlyRoleAdmin = (to, from, next) => {
    //console.log("onlyRoleAdmin");
    let AuthUser = localStorage.getItem('user');
    if (AuthUser) {
        AuthUser = JSON.parse(AuthUser);
        if(AuthUser.role  && AuthUser.role.name === 'admin' ){
            next();
            return;
        }
    }
    next(from.path);
};

const checkRole = async (to, from, next) => {
    let AuthUser = localStorage.getItem('user');
    let AuthUserJson = JSON.parse(AuthUser);
    try{
        if(Vue.prototype.$accessRoles.SidebarNavs === undefined){
            const response  = await axios.get(configs.apiUrl + "/sidebar_navs_opened_access",{headers:{Authorization: 'Bearer ' + localStorage.getItem('token')}});
            if(response.data.success === true) {
                Vue.prototype.$accessRoles  = response.data;
            }
        }
        if (AuthUser) {
            let roles = Vue.prototype.$accessRoles.SidebarNavs;
            if(AuthUserJson.role.name === 'admin'){
                next();
                return;
            }
            let name = to.name.split('.');
            if(roles[name[0]].includes(AuthUserJson.role.name)){
                next();
                return;
            }
        }
        next(from.path);
    }
    catch (e) {
        console.log(e);
        next(from.path);
    }
};

const router =  new Router({
  mode: 'hash', // https://router.vuejs.org/api/#mode
  linkActiveClass: 'open active',
  scrollBehavior: () => ({ y: 0 }),
  routes: [
      {
          path: '/login',
          name: 'login',
          meta: {
              title: "Вход"
          },
          component: Login
      },
      {
          path: '/',
          redirect: '/dashboard',
          meta: {
              title: "Главная",
              label: "Главная"
          },
          component: DefaultContainer,
          beforeEnter: ifNotAuthenticated,
          children: [
              {
                  path: 'dashboard',
                  name: 'dashboard',
                  component: Dashboard,
                  meta: {
                      title: "Главная",
                  }
              },
              {
                  path: 'projects',
                  redirect: '/projects',
                  meta: {
                      label: "Проекты",
                  },
                  component: {
                      render(c) {
                          return c('router-view')
                      }
                  },
                  children: [
                      {
                          path: '',
                          name: 'project',
                          meta: {
                              title: "Проекты"
                          },
                          component: Projects,
                      },
                      {
                          path: 'form/:id?/:newCreated?',
                          name: 'project.form',
                          meta: {
                              title: "Проекты | Форма",
                              label: "Форма"
                          },
                          component: ProjectsForm
                      }
                  ]
              },
              {
                  path: 'dialogs',
                  name: 'dialogs',
                  component: Dialog,
                  meta: {
                      title: "Диалог",
                      label: "Диалог"
                  },
              },
              {
                  path: 'control',
                  redirect: '/control/templates',
                  name: 'templates',
                  meta: {
                      title: "Управление",
                      label: "Управление"
                  },
                  component: {
                      render(c) {
                          return c('router-view')
                      }
                  },
                  children: [
                      {
                          path: 'templates',
                          meta: {
                              title: "Шаблоны",
                              label: "Шаблоны"
                          },
                          component: {
                              render(c) {
                                  return c('router-view')
                              }
                          },
                          children :[
                              {
                                  path: '',
                                  name: 'template',
                                  meta: {
                                      title: "Шаблоны"
                                  },
                                  component: Templates
                              },
                              {
                                  path: 'form/:id?',
                                  name: 'template.form',
                                  meta: {
                                      title: "Шаблоны | Форма",
                                      label: "Форма"
                                  },
                                  component: TemplatesForm
                              }
                          ]
                      },{
                          path: 'self-management-menu',
                          meta: {
                              title: "Меню",
                              label: "Меню"
                          },
                          component: {
                              render(c) {
                                  return c('router-view')
                              }
                          },
                          children:[
                              {
                                  path: '',
                                  name: 'self-management-menu',
                                  meta: {
                                      title: "Меню",
                                  },
                                  component: SelfManagementMenu,
                              },
                              {
                                  path: 'form/:id?',
                                  name: 'self-management-menu.form',
                                  meta: {
                                      title: "Меню | Форма",
                                      label: "Форма"
                                  },
                                  component: SelfManagementMenuForm
                              },
                          ],
                      },
                  ]
              }, {
                  path: 'sequences',
                  redirect: '/sequences/list',
                  name: 'Последовательности',
                  meta: {
                      label: "Последовательности",
                  },
                  component: {
                      render(c) {
                          return c('router-view')
                      }
                  },
                  children: [
                      {
                          path: 'list',
                          //name: 'sequences_list',
                          meta: {
                              title: "Список",
                              label: "Список"
                          },
                          component: {
                              render(c) {
                                  return c('router-view')
                              }
                          },
                          children:[
                              {
                                  path: '',
                                  name: 'sequence',
                                  meta: {
                                      title: "Последовательности"
                                  },
                                  component: SequencesList,
                              },
                              {
                                  path: 'form/:id?/:newCreated?',
                                  name: 'sequence.form',
                                  meta: {
                                      title: "Последовательности | Форма",
                                      label: "Форма"
                                  },
                                  component: SequenceForm
                              }
                          ]
                      },
                      {
                          path: 'leads',
                          meta: {
                              title: "Список лидов",
                              label: "Список лидов"
                          },
                          component: {
                              render(c) {
                                  return c('router-view')
                              }
                          },
                          children:[
                              {
                                  path: '',
                                  name: 'leads',
                                  meta: {
                                      title: "Лиды"
                                  },
                                  component: LeadsList,
                              },
                              {
                                  path: 'form/:id?/:newCreated?',
                                  name: 'leads.form',
                                  meta: {
                                      title: "Лиды | Форма",
                                      label: "Форма"
                                  },
                                  component: LeadsForm
                              }
                          ]
                      },
                  ]
              }, {
                  path: 'project-settings',
                  redirect: '/project-settings/users',
                  name: 'Настройки',
                  meta: {
                      label: "Настройки"
                  },
                  component: {
                      render(c) {
                          return c('router-view')
                      }
                  },
                  children: [
                      {
                          path: 'users',
                          meta: {
                              title: "Пользователи",
                              label: "Пользователи"
                          },
                          component: {
                              render(c) {
                                  return c('router-view')
                              }
                          },
                          children:[
                              {
                                  path: '',
                                  name: 'user',
                                  meta: {
                                      title: "Пользователи",
                                  },
                                  component: Users,
                              }, {
                                  path: 'form/:id?',
                                  name: 'user.form',
                                  meta: {
                                      title: "Пользователи | Форма",
                                      label: "Форма"
                                  },
                                  component: UsersForm,
                              },
                          ]
                      },
                      {
                          path: 'roles',
                          meta: {
                              title: "Роли",
                              label: "Роли"
                          },
                          component: {
                              render(c) {
                                  return c('router-view')
                              }
                          },
                          children:[
                              {
                                  path: '',
                                  name: 'role',
                                  meta: {
                                      title: "Роли",
                                  },
                                  component: Roles
                              }, {
                                  path: 'form/:id?',
                                  name: 'role.form',
                                  meta: {
                                      title: "Роли | Форма",
                                      label: "Форма"
                                  },
                                  component: RolesForm
                              },
                          ]
                      },
                      {
                          path: 'events',
                          meta: {
                              title: "События",
                              label: "События",
                          },
                          component: {
                              render(c) {
                                  return c('router-view')
                              }
                          },
                          children:[
                              {
                                  path: '',
                                  name: 'event',
                                  meta: {
                                      title: "События",
                                  },
                                  component: Events
                              }, {
                                  path: 'form/:id?',
                                  name: 'event.form',
                                  meta: {
                                      title: "События | Форма",
                                      label: "Форма"
                                  },
                                  component: EventsForm
                              },
                          ]
                      },
                      {
                          path: 'receivers',
                          meta: {
                              title: "Получатели",
                              label: "Получатели"
                          },
                          component: {
                              render(c) {
                                  return c('router-view')
                              }
                          },
                          children:[
                              {
                                  path: '',
                                  name: 'receiver',
                                  meta: {
                                      title: "Получатели",
                                  },
                                  component: Receivers
                              }, {
                                  path: 'form/:id?',
                                  name: 'receiver.form',
                                  meta: {
                                      title: "Получатели | Форма",
                                      label: "Форма"
                                  },
                                  component: ReceiversForm
                              },
                          ]
                      },
                      {
                          path: 'gateways',
                          meta: {
                              title: "Справочник допустимых шлюзов",
                              label: "Справочник допустимых шлюзов",
                          },
                          component: {
                              render(c) {
                                  return c('router-view')
                              }
                          },
                          children: [
                              {
                                  path: '',
                                  meta: {
                                      title: "Справочник допустимых шлюзов",
                                  },
                                  name: 'gateway',
                                  component: Gateway
                              },
                              {
                                  path: 'form/:id?',
                                  meta: {
                                      title: "Справочник допустимых шлюзов | Форма",
                                      label: "Форма"
                                  },
                                  name: 'gateway.form',
                                  component: GatewayForm
                              },
                          ]
                      },
                      {
                          path: 'sidebar_nav_settings',
                          meta: {
                              title: "Боковая панель",
                              label: "Боковая панель"
                          },
                          component: {
                              render(c) {
                                  return c('router-view')
                              }
                          },
                          children:[
                              {
                                  path: '',
                                  meta: {
                                      title: "Боковая панель",
                                  },
                                  name: 'sidebarNavSetting',
                                  component: SidebarNavSetting,
                                  beforeEnter: onlyRoleAdmin
                              },
                              {
                                  path: 'form/:id',
                                  meta: {
                                      title: "Боковая панель | Форма",
                                      label: "Форма"
                                  },
                                  name: 'sidebarNavSetting.form',
                                  component: SidebarNavSettingForm,
                                  beforeEnter: onlyRoleAdmin
                              }
                          ],
                          beforeEnter: onlyRoleAdmin
                      },
                  ]
              },








        {
          path: 'theme',
          redirect: '/theme/colors',
          name: 'Theme',
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: 'colors',
              name: 'Colors',
              component: Colors,
            },
            {
              path: 'typography',
              name: 'Typography',
              component: Typography,
            }
          ]
        },
        {
          path: 'charts',
          name: 'Charts',
          component: Charts
        },
        {
          path: 'widgets',
          name: 'Widgets',
          component: Widgets
        },
        /*{
          path: 'users',
          meta: { label: 'Users'},
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: '',
              component: Users,
            },
            {
              path: ':id',
              meta: { label: 'User Details'},
              name: 'User',
              component: User,
            },
          ]
        },*/
        {
          path: 'base',
          redirect: '/base/cards',
          name: 'Base',
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: 'cards',
              name: 'Cards',
              component: Cards
            },
            {
              path: 'forms',
              name: 'Forms',
              component: Forms
            },
            {
              path: 'switches',
              name: 'Switches',
              component: Switches
            },
            {
              path: 'tables',
              name: 'Tables',
              component: Tables
            },
            {
              path: 'tabs',
              name: 'Tabs',
              component: Tabs
            },
            {
              path: 'breadcrumbs',
              name: 'Breadcrumbs',
              component: Breadcrumbs
            },
            {
              path: 'carousels',
              name: 'Carousels',
              component: Carousels
            },
            {
              path: 'collapses',
              name: 'Collapses',
              component: Collapses
            },
            {
              path: 'jumbotrons',
              name: 'Jumbotrons',
              component: Jumbotrons
            },
            {
              path: 'list-groups',
              name: 'List Groups',
              component: ListGroups
            },
            {
              path: 'navs',
              name: 'Navs',
              component: Navs
            },
            {
              path: 'navbars',
              name: 'Navbars',
              component: Navbars
            },
            {
              path: 'paginations',
              name: 'Paginations',
              component: Paginations
            },
            {
              path: 'popovers',
              name: 'Popovers',
              component: Popovers
            },
            {
              path: 'progress-bars',
              name: 'Progress Bars',
              component: ProgressBars
            },
            {
              path: 'tooltips',
              name: 'Tooltips',
              component: Tooltips
            }
          ]
        },
        {
          path: 'buttons',
          redirect: '/buttons/standard-buttons',
          name: 'Buttons',
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: 'standard-buttons',
              name: 'Standard Buttons',
              component: StandardButtons
            },
            {
              path: 'button-groups',
              name: 'Button Groups',
              component: ButtonGroups
            },
            {
              path: 'dropdowns',
              name: 'Dropdowns',
              component: Dropdowns
            },
            {
              path: 'brand-buttons',
              name: 'Brand Buttons',
              component: BrandButtons
            }
          ]
        },
        {
          path: 'icons',
          redirect: '/icons/font-awesome',
          name: 'Icons',
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: 'coreui-icons',
              name: 'CoreUI Icons',
              component: CoreUIIcons
            },
            {
              path: 'flags',
              name: 'Flags',
              component: Flags
            },
            {
              path: 'font-awesome',
              name: 'Font Awesome',
              component: FontAwesome
            },
            {
              path: 'simple-line-icons',
              name: 'Simple Line Icons',
              component: SimpleLineIcons
            }
          ]
        },
        {
          path: 'notifications',
          redirect: '/notifications/alerts',
          name: 'Notifications',
          component: {
            render (c) { return c('router-view') }
          },
          children: [
            {
              path: 'alerts',
              name: 'Alerts',
              component: Alerts
            },
            {
              path: 'badges',
              name: 'Badges',
              component: Badges
            },
            {
              path: 'modals',
              name: 'Modals',
              component: Modals
            }
          ]
        }
      ]
    },
    {
      path: '/pages',
      redirect: '/pages/404',
      name: 'Pages',
      component: {
        render (c) { return c('router-view') }
      },
      children: [
        {
          path: '404',
          name: 'Page404',
          component: Page404
        },
        {
          path: '500',
          name: 'Page500',
          component: Page500
        },
        {
          path: 'login',
          name: 'Login',
          component: Login
        },
        {
          path: 'register',
          name: 'Register',
          component: Register
        }
      ]
    },
      /*BOT Admin's Login Route*/
      {   path: "*",
          name: 'any.Page404',
          component: Page404
      }
  ]
});


router.beforeEach((to, from, next) => {

    if(to.name.includes(".form")){
        to.meta.label = "Новый";
        if(to.params.id !== undefined){
            to.meta.label = "Редактирование";
        }
    }

    if(to.meta.title !== undefined && to.meta.title.length){
        document.title = to.meta.title + " | " +location.hostname;
    }
    else{
        document.title = location.hostname;
    }
    next();
    if(to.name !== 'login'){
        ifNotAuthenticated(to, from, next);
    }

});

export default router;