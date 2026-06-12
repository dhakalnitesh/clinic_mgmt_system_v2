<template>
  <div class="doctor-shell">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <div class="sidebar-header">
        <div class="clinic-brand">
          <span class="brand-icon"><i class="ti ti-heartbeat" /></span>
          <transition name="fade">
            <span v-if="!sidebarCollapsed" class="brand-name">MediCare</span>
          </transition>
        </div>
        <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed">
          <i :class="sidebarCollapsed ? 'ti ti-layout-sidebar-right' : 'ti ti-layout-sidebar'" />
        </button>
      </div>

      <div class="doctor-card">
        <div class="doctor-avatar">{{ doctorInitials }}</div>
        <transition name="fade">
          <div v-if="!sidebarCollapsed" class="doctor-info">
            <div class="doctor-name">{{ $page.props.auth.doctor?.name || 'Doctor' }}</div>
            <div class="doctor-spec">{{ $page.props.auth.doctor?.specialization || 'General' }}</div>
          </div>
        </transition>
        <span class="online-dot" :class="dutyStatus" />
      </div>

      <nav class="sidebar-nav">
        <template v-for="group in navGroups" :key="group.label">
          <transition name="fade">
            <div v-if="!sidebarCollapsed" class="nav-group-label">{{ group.label }}</div>
          </transition>
          <Link
            v-for="item in group.items"
            :key="item.route"
            :href="route(item.route)"
            class="nav-item"
            :class="{ active: isActiveRoute(item.route) }"
          >
            <i :class="`ti ${item.icon}`" />
            <transition name="fade">
              <span v-if="!sidebarCollapsed" class="nav-label">{{ item.label }}</span>
            </transition>
            <transition name="fade">
              <span v-if="!sidebarCollapsed && item.badge" class="nav-badge" :class="item.badgeType">
                {{ item.badge }}
              </span>
            </transition>
          </Link>
        </template>
      </nav>
    </aside>

    <!-- Main -->
    <div class="main-area">
      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left">
          <h1 class="page-title">{{ title }}</h1>
          <span class="topbar-date">
            <i class="ti ti-calendar" />
            {{ formattedDate }}
          </span>
        </div>
        <div class="topbar-right">
          <Link :href="route('doctor.consultations.active')" class="btn btn-primary btn-sm">
            <i class="ti ti-stethoscope" /> Start Consultation
          </Link>
          <button class="btn btn-icon" @click="toggleNotifications">
            <i class="ti ti-bell" />
            <span v-if="unreadNotifications" class="notif-dot">{{ unreadNotifications }}</span>
          </button>
          <div class="topbar-avatar" @click="showUserMenu = !showUserMenu">
            {{ doctorInitials }}
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="page-content">
        <slot />
      </main>
    </div>

    <!-- Notification Drawer -->
    <Teleport to="body">
      <div v-if="showNotifications" class="notif-overlay" @click.self="showNotifications = false">
        <div class="notif-drawer">
          <div class="notif-header">
            <span>Notifications</span>
            <button @click="showNotifications = false"><i class="ti ti-x" /></button>
          </div>
          <div class="notif-list">
            <div v-for="n in $page.props.notifications" :key="n.id" class="notif-item" :class="{ unread: !n.read_at }">
              <i :class="`ti ${n.icon}`" />
              <div>
                <div class="notif-msg">{{ n.message }}</div>
                <div class="notif-time">{{ n.time }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
    <Toast />
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Toast from '@/Components/Toast.vue'
// import { route } from 'ziggy-js'
// import {route}
const props = defineProps({
  title: { type: String, default: 'Dashboard' },
})

const page = usePage()
const sidebarCollapsed = ref(false)
const showNotifications = ref(false)
const showUserMenu = ref(false)

const doctorInitials = computed(() => {
  const name = page.props.auth?.doctor?.name || 'DR'
  return name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase()
})

const dutyStatus = computed(() => page.props.auth?.doctor?.on_duty ? 'online' : 'offline')

const unreadNotifications = computed(() =>
  (page.props.notifications || []).filter(n => !n.read_at).length
)

const formattedDate = computed(() =>
  new Intl.DateTimeFormat('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }).format(new Date())
)

function isActiveRoute(routeName) {
  return route().current(routeName + '*') || route().current(routeName)
}

function toggleNotifications() {
  showNotifications.value = !showNotifications.value
}

const navGroups = computed(() => [
  {
    label: 'Main',
    items: [
      { route: 'doctor.dashboard', icon: 'ti-layout-dashboard', label: 'Dashboard' },
    ],
  },
  {
    label: 'Patients',
    items: [
      {
        route: 'doctor.patients.index',
        icon: 'ti-users',
        label: 'My Patients',
        badge: page.props.counts?.patients,
        badgeType: 'teal',
      },
    ],
  },
  {
    label: 'Visits',
    items: [
      {
        route: 'doctor.visits.today',
        icon: 'ti-calendar-check',
        label: "Today's Visits",
        badge: page.props.counts?.todayVisits,
        badgeType: 'amber',
      },
      { route: 'doctor.consultations.index', icon: 'ti-stethoscope', label: 'Consultations' },
    ],
  },
  // {
  //   label: 'Clinical',
  //   items: [
  //     { route: 'doctor.prescriptions.index', icon: 'ti-pill', label: 'Prescriptions' },
  //     {
  //       route: 'doctor.laboratory.index',
  //       icon: 'ti-flask',
  //       label: 'Laboratory',
  //       badge: page.props.counts?.pendingLabs,
  //       badgeType: 'coral',
  //     },
  //     {
  //       route: 'doctor.followups.index',
  //       icon: 'ti-repeat',
  //       label: 'Follow Ups',
  //       badge: page.props.counts?.followupsDue,
  //       badgeType: 'coral',
  //     },
  //   ],
  // },
  {
    label: 'Account',
    items: [
      { route: 'doctor.profile', icon: 'ti-user-circle', label: 'Profile' },
    ],
  },
])
</script>

<style scoped>
.doctor-shell {
  display: flex;
  height: 100vh;
  overflow: hidden;
  background: var(--color-bg-base, #f5f6fa);
  font-family: 'Figtree', system-ui, sans-serif;
}

/* ── Sidebar ─────────────────────────────────── */
.sidebar {
  width: 220px;
  background: #fff;
  border-right: 1px solid #e8eaed;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  transition: width 0.2s ease;
  overflow: hidden;
}
.sidebar.collapsed { width: 60px; }

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 14px 10px;
  border-bottom: 1px solid #f0f1f3;
}
.clinic-brand { display: flex; align-items: center; gap: 8px; }
.brand-icon { font-size: 20px; color: #0F6E56; }
.brand-name { font-size: 14px; font-weight: 600; color: #1a1a2e; white-space: nowrap; }
.collapse-btn {
  background: none; border: none; cursor: pointer;
  color: #9ca3af; font-size: 16px; padding: 2px;
}
.collapse-btn:hover { color: #374151; }

.doctor-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-bottom: 1px solid #f0f1f3;
}
.doctor-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  background: #E1F5EE; color: #0F6E56;
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 600; flex-shrink: 0;
}
.doctor-info { min-width: 0; }
.doctor-name { font-size: 12px; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.doctor-spec { font-size: 11px; color: #6b7280; }
.online-dot { width: 8px; height: 8px; border-radius: 50%; margin-left: auto; flex-shrink: 0; }
.online-dot.online { background: #22c55e; }
.online-dot.offline { background: #9ca3af; }

.sidebar-nav { flex: 1; padding: 8px 0; overflow-y: auto; }
.nav-group-label {
  padding: 8px 14px 2px;
  font-size: 9.5px; font-weight: 600;
  color: #9ca3af; text-transform: uppercase; letter-spacing: .07em;
}
.nav-item {
  display: flex; align-items: center; gap: 9px;
  padding: 8px 14px;
  font-size: 12.5px; color: #4b5563;
  text-decoration: none;
  border-left: 2px solid transparent;
  transition: all .15s;
  white-space: nowrap;
}
.nav-item:hover { color: #111827; background: #f9fafb; }
.nav-item.active { color: #0F6E56; background: #E1F5EE; border-left-color: #0F6E56; font-weight: 600; }
.nav-item i { font-size: 15px; width: 18px; text-align: center; flex-shrink: 0; }
.nav-label { flex: 1; }
.nav-badge {
  font-size: 10px; font-weight: 600; padding: 1px 6px;
  border-radius: 20px; margin-left: auto;
}
.nav-badge.teal { background: #E1F5EE; color: #0F6E56; }
.nav-badge.amber { background: #FAEEDA; color: #BA7517; }
.nav-badge.coral { background: #FAECE7; color: #993C1D; }

/* ── Main ─────────────────────────────────── */
.main-area { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

.topbar {
  height: 56px; background: #fff;
  border-bottom: 1px solid #e8eaed;
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 20px; gap: 12px; flex-shrink: 0;
}
.topbar-left { display: flex; align-items: center; gap: 12px; }
.page-title { font-size: 15px; font-weight: 600; color: #111827; margin: 0; }
.topbar-date { font-size: 12px; color: #9ca3af; display: flex; align-items: center; gap: 4px; }
.topbar-right { display: flex; align-items: center; gap: 8px; }
.topbar-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: #E1F5EE; color: #0F6E56;
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 600; cursor: pointer;
}

.btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 14px; border-radius: 8px; font-size: 12.5px;
  font-family: inherit; cursor: pointer; border: 1px solid #e5e7eb;
  background: #fff; color: #374151; text-decoration: none; transition: all .15s;
}
.btn:hover { background: #f9fafb; }
.btn-primary { background: #0F6E56; color: #fff; border-color: #0F6E56; }
.btn-primary:hover { background: #1D9E75; border-color: #1D9E75; }
.btn-sm { padding: 5px 10px; font-size: 12px; }
.btn-icon { padding: 7px; position: relative; }
.notif-dot {
  position: absolute; top: 4px; right: 4px;
  background: #E24B4A; color: #fff;
  font-size: 9px; font-weight: 700;
  width: 15px; height: 15px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
}

.page-content { flex: 1; overflow-y: auto; padding: 20px; }

/* ── Notification Drawer ─────────────────────────────────── */
.notif-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,.25); z-index: 200;
}
.notif-drawer {
  position: fixed; top: 0; right: 0; bottom: 0; width: 320px;
  background: #fff; border-left: 1px solid #e8eaed;
  display: flex; flex-direction: column;
}
.notif-header {
  padding: 16px; border-bottom: 1px solid #f0f1f3;
  display: flex; justify-content: space-between; align-items: center;
  font-size: 14px; font-weight: 600;
}
.notif-header button { background: none; border: none; cursor: pointer; font-size: 16px; color: #6b7280; }
.notif-list { flex: 1; overflow-y: auto; }
.notif-item {
  display: flex; align-items: flex-start; gap: 10px;
  padding: 12px 16px; border-bottom: 1px solid #f9fafb; font-size: 12.5px;
}
.notif-item.unread { background: #f0fdf9; }
.notif-msg { color: #111827; line-height: 1.4; }
.notif-time { font-size: 11px; color: #9ca3af; margin-top: 2px; }

/* ── Transition ─────────────────────────────────── */
.fade-enter-active, .fade-leave-active { transition: opacity .15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>