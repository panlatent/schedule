import Vue from 'vue';
import Router from 'vue-router';
import GridLogs from "@/components/GridLogs.vue";

Vue.use(Router);

export default new Router({
  mode: 'history',
  base: (<any>window).Craft.schedule.logs.cpTrigger,
  routes: [
    {
      path: '/schedule/logs/:handle',
      name: 'logs',
      component: GridLogs
    },
  ],
});
