<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Files,
    ImageIcon,
    ImagePlus,
    LayoutGrid,
    SquarePen,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';
import { dashboard } from '@/routes';
import { create, index } from '@/routes/dashboard/posts';
import {
    create as createRealization,
    index as indexRealizations,
} from '@/routes/dashboard/realizations';

const page = usePage();
const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Strona główna',
        href: dashboard(),
        icon: LayoutGrid,
    },
    ...(page.props.auth.user.is_admin
        ? [
              {
                  title: 'Posty',
                  href: index(),
                  icon: Files,
              },
              {
                  title: 'Nowy artykuł',
                  href: create(),
                  icon: SquarePen,
              },
              {
                  title: 'Realizacje',
                  href: indexRealizations(),
                  icon: ImageIcon,
              },
              {
                  title: 'Nowa realizacja',
                  href: createRealization(),
                  icon: ImagePlus,
              },
          ]
        : []),
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
