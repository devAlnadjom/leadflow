<script setup lang="ts">
import { ref } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { Bell, Check } from 'lucide-vue-next';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

const page = usePage();
const auth = page.props.auth as any;
const notifications = ref(auth.notifications || []);
const unreadCount = ref(auth.unreadNotificationsCount || 0);

const markAsRead = (id: string) => {
    router.post(`/notifications/${id}/read`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            const notif = notifications.value.find((n: any) => n.id === id);
            if (notif) notif.read_at = new Date().toISOString();
            if (unreadCount.value > 0) unreadCount.value--;
        }
    });
};

const markAllAsRead = () => {
    router.post('/notifications/read-all', {}, {
        preserveScroll: true,
        onSuccess: () => {
            notifications.value.forEach((n: any) => n.read_at = new Date().toISOString());
            unreadCount.value = 0;
        }
    });
};
</script>

<template>
  <Popover>
    <PopoverTrigger asChild>
      <Button variant="ghost" size="icon" class="relative text-slate-500 hover:text-slate-900 focus:outline-none">
        <Bell class="w-5 h-5" />
        <Badge v-if="unreadCount > 0" class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 p-0 text-[10px] text-white">
          {{ unreadCount > 9 ? '9+' : unreadCount }}
        </Badge>
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-80 p-0" align="end">
      <div class="flex items-center justify-between border-b px-4 py-3">
        <h4 class="font-semibold text-sm">Notifications</h4>
        <Button v-if="unreadCount > 0" variant="ghost" size="sm" class="h-auto p-0 text-xs text-indigo-600 hover:text-indigo-700" @click="markAllAsRead">
          Tout marquer comme lu
        </Button>
      </div>
      <div class="max-h-[300px] overflow-y-auto">
        <div v-if="notifications.length === 0" class="py-6 text-center text-sm text-slate-500">
          Aucune notification
        </div>
        <div v-else class="flex flex-col">
          <div 
            v-for="notification in notifications" 
            :key="notification.id"
            class="flex items-start gap-3 border-b p-4 transition-colors last:border-0 hover:bg-slate-50 relative group cursor-pointer"
            :class="[notification.read_at ? 'opacity-70' : 'bg-slate-50/50']"
          >
            <div class="mt-1 h-2 w-2 shrink-0 rounded-full" :class="[notification.read_at ? 'bg-transparent' : 'bg-indigo-600']"></div>
            <div class="flex-1 space-y-1">
                <Link v-if="notification.data.lead_id" :href="`/leads/${notification.data.lead_id}`" class="block" @click="markAsRead(notification.id)">
                  <p class="text-sm font-medium leading-none">{{ notification.data.message || 'Nouvelle notification' }}</p>
                  <p class="text-xs text-slate-500 mt-1" v-if="notification.data.form_name">
                      Source : {{ notification.data.form_name }}
                  </p>
                  <p class="text-xs text-slate-400 mt-1">
                    {{ new Date(notification.created_at).toLocaleDateString() }} à {{ new Date(notification.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                  </p>
                </Link>
                <div v-else>
                  <p class="text-sm font-medium leading-none">{{ notification.data.message || 'Nouvelle notification' }}</p>
                  <p class="text-xs text-slate-400 mt-1">
                    {{ new Date(notification.created_at).toLocaleDateString() }} à {{ new Date(notification.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                  </p>
                </div>
            </div>
            <Button 
                v-if="!notification.read_at" 
                variant="ghost" 
                size="icon" 
                class="absolute right-2 top-2 h-6 w-6 opacity-0 transition-opacity group-hover:opacity-100" 
                @click.prevent.stop="markAsRead(notification.id)"
                title="Marquer comme lu"
            >
                <Check class="h-3.5 w-3.5 text-slate-400" />
            </Button>
          </div>
        </div>
      </div>
      <div class="border-t p-2 text-center" v-if="notifications.length > 0">
          <Link href="/leads" class="text-xs font-medium text-slate-500 hover:text-indigo-600">
              Voir tous les leads
          </Link>
      </div>
    </PopoverContent>
  </Popover>
</template>
