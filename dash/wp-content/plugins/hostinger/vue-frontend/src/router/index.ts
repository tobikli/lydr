import { createRouter, createMemoryHistory } from 'vue-router';
import baseRoutes from '@/router/baseRoutes';


const router = createRouter({
  history: createMemoryHistory(),
  routes: baseRoutes
});


export default router;
