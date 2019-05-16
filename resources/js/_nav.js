export default {
  items: [
      {
          routeName:"dashboard",
          name: 'Главная',
          url: '/dashboard',
          icon: 'icon-speedometer',
          badge: {
                variant: 'primary',
                text: 'NEW'
            }
      },
      {
          routeName:"project",
          name: 'Проекты',
          url: '/projects',
          icon: 'icon-grid'
      },
      {
          routeName:"dialog",
          name: 'Диалог',
          url: '/dialogs',
          icon: 'icon-speech'
      },
      {
          name: 'Управление',
          url: '/control',
          icon: 'icon-game-controller',
          children: [
              {
                  routeName:"template",
                  name: 'Шаблоны',
                  url: '/control/templates',
                  icon: 'icon-docs'
              },
              {
                  routeName:"self-management-menu",
                  name: 'Меню',
                  url: '/control/self-management-menu',
                  icon: 'icon-puzzle'
              }
          ]
      },
      /*{
          name: 'Чаты с клиентами',
          url: '/chats',
          icon: 'icon-pie-chart'
      },*/
      {
          name: 'AI System',
          url: '/ai-system',
          icon: 'icon-puzzle',
          children: [
              /*{
                  name: 'Базовые установки',
                  url: '/ai-system/base-settings',
                  icon: 'icon-puzzle'
              },
              {
                  name: 'Сущности',
                  url: '/ai-system/entities',
                  icon: 'icon-puzzle'
              },*/
          ]
      },
      {
          name: 'Последовательности',
          url: '/sequences',
          icon: 'icon-puzzle',
          children: [
              {
                  routeName:"sequences_list",
                  name: 'Редактирование',
                  url: '/sequences/list',
                  icon: 'icon-grid'
              },
              {
                  routeName:"leads",
                  name: 'Список лидов',
                  url: '/sequences/leads',
                  icon: 'icon-trophy'
              }
          ]
      },
      {
          name: 'Настройки',
          url: '/project-settings',
          icon: 'icon-settings',
          children: [
              {
                  routeName:"user",
                  name: 'Пользователи',
                  url: '/project-settings/users',
                  icon: 'icon-people'
              },
              {
                  routeName:"role",
                  name: 'Роли',
                  url: '/project-settings/roles',
                  icon: 'icon-user-following'
              },
              {
                  routeName:"event",
                  name: 'События',
                  url: '/project-settings/events',
                  icon: 'icon-rocket'
              },
              {
                  routeName:"receiver",
                  name: 'Получатели',
                  url: '/project-settings/receivers',
                  icon: 'icon-shuffle'
              },
              {
                  routeName:"gateway",
                  name: 'Справочник шлюзов',
                  url: '/project-settings/gateways',
                  icon: 'icon-vector'
              },
              {
                  routeName:"sidebar_nav_setting",
                  name: 'Боковая панель',
                  url: '/project-settings/sidebar_nav_settings',
                  icon: 'icon-menu'
              }
          ]
      },




    /*{
      title: true,
      name: 'Theme',
      class: '',
      wrapper: {
        element: '',
        attributes: {}
      }
    },
    {
      name: 'Colors',
      url: '/theme/colors',
      icon: 'icon-drop'
    },
    {
      name: 'Typography',
      url: '/theme/typography',
      icon: 'icon-pencil'
    },
    {
      title: true,
      name: 'Components',
      class: '',
      wrapper: {
        element: '',
        attributes: {}
      }
    },
    {
      name: 'Base',
      url: '/base',
      icon: 'icon-puzzle',
      children: [
        {
          name: 'Breadcrumbs',
          url: '/base/breadcrumbs',
          icon: 'icon-puzzle'
        },
        {
          name: 'Cards',
          url: '/base/cards',
          icon: 'icon-puzzle'
        },
        {
          name: 'Carousels',
          url: '/base/carousels',
          icon: 'icon-puzzle'
        },
        {
          name: 'Collapses',
          url: '/base/collapses',
          icon: 'icon-puzzle'
        },
        {
          name: 'Forms',
          url: '/base/forms',
          icon: 'icon-puzzle'
        },
        {
          name: 'Jumbotrons',
          url: '/base/jumbotrons',
          icon: 'icon-puzzle'
        },
        {
          name: 'List Groups',
          url: '/base/list-groups',
          icon: 'icon-puzzle'
        },
        {
          name: 'Navs',
          url: '/base/navs',
          icon: 'icon-puzzle'
        },
        {
          name: 'Navbars',
          url: '/base/navbars',
          icon: 'icon-puzzle'
        },
        {
          name: 'Paginations',
          url: '/base/paginations',
          icon: 'icon-puzzle'
        },
        {
          name: 'Popovers',
          url: '/base/popovers',
          icon: 'icon-puzzle'
        },
        {
          name: 'Progress Bars',
          url: '/base/progress-bars',
          icon: 'icon-puzzle'
        },
        {
          name: 'Switches',
          url: '/base/switches',
          icon: 'icon-puzzle'
        },
        {
          name: 'Tables',
          url: '/base/tables',
          icon: 'icon-puzzle'
        },
        {
          name: 'Tabs',
          url: '/base/tabs',
          icon: 'icon-puzzle'
        },
        {
          name: 'Tooltips',
          url: '/base/tooltips',
          icon: 'icon-puzzle'
        }
      ]
    },
    {
      name: 'Buttons',
      url: '/buttons',
      icon: 'icon-cursor',
      children: [
        {
          name: 'Buttons',
          url: '/buttons/standard-buttons',
          icon: 'icon-cursor'
        },
        {
          name: 'Button Dropdowns',
          url: '/buttons/dropdowns',
          icon: 'icon-cursor'
        },
        {
          name: 'Button Groups',
          url: '/buttons/button-groups',
          icon: 'icon-cursor'
        },
        {
          name: 'Brand Buttons',
          url: '/buttons/brand-buttons',
          icon: 'icon-cursor'
        }
      ]
    },
    {
      name: 'Charts',
      url: '/charts',
      icon: 'icon-pie-chart'
    },
    {
      name: 'Icons',
      url: '/icons',
      icon: 'icon-star',
      children: [
        {
          name: 'CoreUI Icons',
          url: '/icons/coreui-icons',
          icon: 'icon-star',
          badge: {
            variant: 'info',
            text: 'NEW'
          }
        },
        {
          name: 'Flags',
          url: '/icons/flags',
          icon: 'icon-star'
        },
        {
          name: 'Font Awesome',
          url: '/icons/font-awesome',
          icon: 'icon-star',
          badge: {
            variant: 'secondary',
            text: '4.7'
          }
        },
        {
          name: 'Simple Line Icons',
          url: '/icons/simple-line-icons',
          icon: 'icon-star'
        }
      ]
    },
    {
      name: 'Notifications',
      url: '/notifications',
      icon: 'icon-bell',
      children: [
        {
          name: 'Alerts',
          url: '/notifications/alerts',
          icon: 'icon-bell'
        },
        {
          name: 'Badges',
          url: '/notifications/badges',
          icon: 'icon-bell'
        },
        {
          name: 'Modals',
          url: '/notifications/modals',
          icon: 'icon-bell'
        }
      ]
    },
    {
      name: 'Widgets',
      url: '/widgets',
      icon: 'icon-calculator',
      badge: {
        variant: 'primary',
        text: 'NEW'
      }
    },
    {
      divider: true
    },
    {
      title: true,
      name: 'Extras'
    },
    {
      name: 'Pages',
      url: '/pages',
      icon: 'icon-star',
      children: [
        {
          name: 'Login',
          url: '/pages/login',
          icon: 'icon-star'
        },
        {
          name: 'Register',
          url: '/pages/register',
          icon: 'icon-star'
        },
        {
          name: 'Error 404',
          url: '/pages/404',
          icon: 'icon-star'
        },
        {
          name: 'Error 500',
          url: '/pages/500',
          icon: 'icon-star'
        }
      ]
    },
    {
      name: 'Download CoreUI',
      url: 'http://coreui.io/vue/',
      icon: 'icon-cloud-download',
      class: 'mt-auto',
      variant: 'success'
    },
    {
      name: 'Try CoreUI PRO',
      url: 'http://coreui.io/pro/vue/',
      icon: 'icon-layers',
      variant: 'danger'
    }*/
  ]
}
