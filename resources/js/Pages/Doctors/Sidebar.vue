<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

function safeRoute(name, params) {
    try { return route(name, params) } catch { return '#' }
}

const page = usePage()

const doctor  = computed(() => page.props.auth?.doctor ?? {})
const badges  = computed(() => page.props.badges  ?? {})

const greeting = computed(() => {
  const h = new Date().getHours()
  return h < 12 ? 'Good Morning' : h < 17 ? 'Good Afternoon' : 'Good Evening'
})

const today = computed(() =>
  new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
)

const navGroups = [
  {
    group: null,
    items: [
      { id: 'dashboard',      label: 'Dashboard',       icon: 'ti-layout-dashboard',  route: 'doctor.dashboard' },
    ],
  },
  {
    group: 'Patients',
    items: [
      { id: 'patients',       label: 'My Patients',     icon: 'ti-users',              route: 'doctor.patients.index' },
    ],
  },
  {
    group: 'Visits',
    items: [
      { id: 'visits',         label: "Today's Visits",  icon: 'ti-calendar-event',     route: 'doctor.visits.today',        badge: 'queue' },
    ],
  },
  {
    group: null,
    items: [
      { id: 'consultations',  label: 'Consultations',   icon: 'ti-stethoscope',        route: 'doctor.consultations.index' },
      { id: 'prescriptions',  label: 'Prescriptions',   icon: 'ti-pill',               route: 'doctor.prescriptions.index' },
    ],
  },
  // {
  //   group: 'Laboratory',
  //   items: [
  //     { id: 'lab-orders',     label: 'Lab Orders',      icon: 'ti-flask',              route: 'doctor.lab.orders' },
  //     { id: 'lab-results',    label: 'Lab Results',     icon: 'ti-microscope',         route: 'doctor.lab.results',         badge: 'pendingLabs' },
  //   ],
  // },
  {
    group: null,
    items: [
      // { id: 'followups',      label: 'Follow Ups',      icon: 'ti-calendar-repeat',    route: 'doctor.followups.index',     badge: 'followUps' },
      { id: 'profile',        label: 'Profile',         icon: 'ti-user-circle',        route: 'doctor.profile' },
    ],
  },
]

const isActive = (routeName) => {
  try { return route().current(routeName) || route().current(routeName + '.*') }
  catch { return false }
}

const statusOptions = ['Available', 'Busy', 'Break']
</script>

<template>
  <div class="doctor-shell">

    <!-- ─── Sidebar ─────────────────────────────────────── -->
    <aside class="sidebar">

      <!-- Logo -->
      <div class="sidebar-logo">
        <div class="logo-icon">
          <i class="ti ti-heartbeat" aria-hidden="true"></i>
        </div>
        <div>
          <div class="logo-name">MediCore</div>
          <div class="logo-sub">Clinic Manager</div>
        </div>
      </div>

      <!-- Doctor card -->
      <div class="doctor-card">
        <div class="doctor-avatar">
          {{ doctor.initials ?? 'DR' }}
          <span class="online-dot"></span>
        </div>
        <div class="doctor-info">
          <div class="doctor-name">{{ doctor.name ?? 'Dr. Rajesh Sharma' }}</div>
          <div class="doctor-spec">{{ doctor.specialty ?? 'Internal Medicine' }}</div>
        </div>
      </div>

      <!-- Availability -->
      <div class="status-row">
        <button
          v-for="s in statusOptions" :key="s"
          class="status-btn"
          :class="{ 'status-active': s === 'Available' }"
        >{{ s }}</button>
      </div>

      <!-- Navigation -->
      <nav class="nav-list" aria-label="Doctor navigation">
        <template v-for="group in navGroups" :key="group.group ?? '_root'">
          <p v-if="group.group" class="nav-group-label">{{ group.group }}</p>

          <Link
            v-for="item in group.items" :key="item.id"
            :href="safeRoute(item.route)"
            class="nav-item"
            :class="{ 'nav-item--active': isActive(item.route) }"
          >
            <i :class="['ti', item.icon]" aria-hidden="true"></i>
            <span>{{ item.label }}</span>
            <span
              v-if="item.badge && badges[item.badge]"
              class="nav-badge"
              :class="item.badge === 'followUps' ? 'nav-badge--amber' : 'nav-badge--red'"
            >{{ badges[item.badge] }}</span>
          </Link>
        </template>
      </nav>

      <div class="sidebar-footer">MediCore v2.4 · {{ new Date().getFullYear() }}</div>
    </aside>

    <!-- ─── Main column ──────────────────────────────────── -->
    <div class="main-col">

      <!-- Header -->
      <header class="top-bar">
        <div>
          <h1 class="top-greeting">{{ greeting }}, {{ doctor.short_name ?? 'Dr. Sharma' }}</h1>
          <p class="top-date">{{ today }}</p>
        </div>

        <div class="top-actions">
          <!-- Patient search -->
          <label class="search-box">
            <i class="ti ti-search" aria-hidden="true"></i>
            <input placeholder="Search patients…" class="search-input" />
          </label>

          <!-- Live queue pill -->
          <div class="queue-pill" aria-label="Patients in queue">
            <span class="queue-dot"></span>
            <span>{{ badges.queue ?? 0 }} in Queue</span>
          </div>

          <!-- Notifications -->
          <div class="notif-btn" role="button" tabindex="0" aria-label="Notifications">
            <i class="ti ti-bell" aria-hidden="true"></i>
            <span v-if="badges.notifications" class="notif-badge">{{ badges.notifications }}</span>
          </div>
        </div>
      </header>

      <!-- Page slot -->
      <main class="page-area">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped>
/* ── Tokens ──────────────────────────────────────────────── */
:root {
  --bg:          #080C14;
  --sidebar:     #0D1321;
  --card:        #111827;
  --border:      #1A2540;
  --accent:      #0ED7A0;
  --accent-dim:  rgba(14,215,160,.10);
  --amber:       #F5A623;
  --red:         #FF4D6D;
  --blue:        #4D9EFF;
  --purple:      #A78BFA;
  --tp:          #E8EDF8;   /* text primary   */
  --ts:          #7B8DB5;   /* text secondary */
  --tm:          #3D4F6E;   /* text muted     */
}

/* ── Shell ───────────────────────────────────────────────── */
.doctor-shell {
  display: flex;
  height: 100vh;
  overflow: hidden;
  background: var(--bg);
  font-family: 'Inter', system-ui, sans-serif;
  color: var(--tp);
}

/* ── Sidebar ─────────────────────────────────────────────── */
.sidebar {
  width: 240px;
  flex-shrink: 0;
  background: var(--sidebar);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px 18px 16px;
  border-bottom: 1px solid var(--border);
}
.logo-icon {
  width: 32px; height: 32px;
  border-radius: 8px;
  background: var(--accent-dim);
  border: 1.5px solid color-mix(in srgb, var(--accent) 30%, transparent);
  display: flex; align-items: center; justify-content: center;
  color: var(--accent); font-size: 16px;
}
.logo-name { font-size: 14px; font-weight: 800; color: var(--tp); letter-spacing: -.3px; }
.logo-sub  { font-size: 10px; color: var(--ts); }

/* Doctor card */
.doctor-card {
  display: flex; align-items: center; gap: 10px;
  padding: 14px 18px;
  border-bottom: 1px solid var(--border);
}
.doctor-avatar {
  position: relative;
  width: 40px; height: 40px; border-radius: 10px;
  background: linear-gradient(135deg, color-mix(in srgb, var(--accent) 20%, transparent), color-mix(in srgb, var(--blue) 20%, transparent));
  border: 1.5px solid color-mix(in srgb, var(--accent) 35%, transparent);
  display: flex; align-items: center; justify-content: center;
  color: var(--accent); font-size: 13px; font-weight: 700; flex-shrink: 0;
}
.online-dot {
  position: absolute; bottom: 2px; right: 2px;
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--accent);
  box-shadow: 0 0 6px var(--accent);
  border: 1.5px solid var(--sidebar);
}
.doctor-info { flex: 1; min-width: 0; }
.doctor-name { font-size: 13px; font-weight: 700; color: var(--tp); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.doctor-spec { font-size: 10px; color: var(--ts); }

/* Status row */
.status-row {
  display: flex; gap: 4px;
  padding: 0 18px 14px;
  border-bottom: 1px solid var(--border);
}
.status-btn {
  flex: 1; padding: 4px 0; border-radius: 4px; border: none; cursor: pointer;
  font-size: 9px; font-weight: 700; letter-spacing: .4px; background: var(--border); color: var(--ts);
  transition: all .15s;
}
.status-btn:hover { color: var(--tp); }
.status-active { background: var(--accent-dim) !important; color: var(--accent) !important; }

/* Nav */
.nav-list  { flex: 1; padding: 10px 10px; }
.nav-group-label {
  font-size: 9px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase;
  color: var(--tm); padding: 12px 8px 4px; margin: 0;
}
.nav-item {
  display: flex; align-items: center; gap: 9px;
  padding: 8px 10px; border-radius: 8px; margin-bottom: 2px;
  font-size: 13px; font-weight: 500; color: var(--ts);
  text-decoration: none; transition: all .15s;
  border-left: 2px solid transparent;
}
.nav-item:hover { background: #182033; color: var(--tp); }
.nav-item .ti { font-size: 16px; width: 18px; text-align: center; }
.nav-item span:nth-child(2) { flex: 1; }
.nav-item--active {
  background: var(--accent-dim);
  color: var(--accent);
  border-left-color: var(--accent);
  font-weight: 600;
}
.nav-badge {
  font-size: 10px; font-weight: 700;
  padding: 1px 7px; border-radius: 20px;
}
.nav-badge--amber { background: color-mix(in srgb, var(--amber) 20%, transparent); color: var(--amber); }
.nav-badge--red   { background: color-mix(in srgb, var(--red)   20%, transparent); color: var(--red); }

.sidebar-footer {
  padding: 12px 18px;
  border-top: 1px solid var(--border);
  font-size: 10px; color: var(--tm);
}

/* ── Main ────────────────────────────────────────────────── */
.main-col {
  flex: 1; display: flex; flex-direction: column; overflow: hidden;
}

/* Header */
.top-bar {
  height: 64px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 28px;
  background: var(--sidebar);
  border-bottom: 1px solid var(--border);
}
.top-greeting { font-size: 15px; font-weight: 700; color: var(--tp); margin: 0; }
.top-date     { font-size: 11px; color: var(--ts); margin: 0; }
.top-actions  { display: flex; align-items: center; gap: 12px; }

.search-box {
  display: flex; align-items: center; gap: 8px;
  background: var(--card); border: 1px solid var(--border);
  border-radius: 8px; padding: 8px 12px; width: 220px; cursor: text;
}
.search-box .ti { color: var(--ts); font-size: 14px; }
.search-input {
  background: none; border: none; outline: none;
  color: var(--tp); font-size: 13px; flex: 1;
}
.search-input::placeholder { color: var(--tm); }

.queue-pill {
  display: flex; align-items: center; gap: 6px;
  background: var(--accent-dim);
  border: 1px solid color-mix(in srgb, var(--accent) 25%, transparent);
  border-radius: 8px; padding: 7px 12px;
  font-size: 12px; font-weight: 600; color: var(--accent);
}
.queue-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: var(--accent);
  box-shadow: 0 0 6px var(--accent);
}

.notif-btn {
  position: relative; cursor: pointer;
  width: 36px; height: 36px; border-radius: 8px;
  background: var(--card); border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  color: var(--ts); font-size: 16px;
}
.notif-badge {
  position: absolute; top: -4px; right: -4px;
  width: 16px; height: 16px; border-radius: 50%;
  background: var(--red); color: #fff;
  font-size: 9px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}

.page-area {
  flex: 1; overflow: hidden; display: flex; flex-direction: column;
}

/* ── Scrollbar ───────────────────────────────────────────── */
::-webkit-scrollbar        { width: 4px; height: 4px; }
::-webkit-scrollbar-track  { background: transparent; }
::-webkit-scrollbar-thumb  { background: var(--border); border-radius: 2px; }
</style>