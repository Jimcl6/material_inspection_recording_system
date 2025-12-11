<template>
  <div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="h3 m-0">Modification Logs</h1>
      <a href="/modification-logs/create" class="btn btn-primary">New</a>
    </div>

    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Model Code</th>
                <th>Item</th>
                <th>Recorded By</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in logs.data" :key="r.id">
                <td>{{ r.id }}</td>
                <td>{{ r.prod_date }}</td>
                <td>{{ r.model_code }}</td>
                <td>{{ r.item_for_modification }}</td>
                <td>{{ r.recorded_by }}</td>
                <td class="text-end">
                  <div class="dropdown dropup d-inline-block position-static">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" data-bs-boundary="viewport" aria-expanded="false">Actions</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" :href="`/modification-logs/${r.id}`">View</a></li>
                      <li><a class="dropdown-item" :href="`/modification-logs/${r.id}/edit`">Edit</a></li>
                      <li><hr class="dropdown-divider"></li>
                      <li><button class="dropdown-item text-danger" @click.prevent="destroy(r.id)">Delete</button></li>
                    </ul>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer">
        <nav v-if="logs.links && logs.links.length">
          <ul class="pagination mb-0">
            <li v-for="(l, idx) in logs.links" :key="idx" class="page-item" :class="{ active: l.active, disabled: !l.url }">
              <a class="page-link" :href="l.url || '#'" v-html="l.label"></a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({ logs: Object })
import { router } from '@inertiajs/vue3'

function destroy(id) {
  if (!confirm('Delete this record?')) return
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
  router.post(`/modification-logs/${id}`, { _method: 'delete', _token: csrf }, { preserveScroll: true })
}
</script>
