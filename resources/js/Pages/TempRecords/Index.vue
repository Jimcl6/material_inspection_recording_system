<template>
  <div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div class="px-4 py-5 my-5 text-center">
        <h1 class="display-5 fw-bold text-body-emphasis">
          Centered hero
        </h1>
        <div class="col-lg-6 mx-auto">
          <p class="lead mb-4">
            Quickly design and customize responsive mobile-first
            sites with Bootstrap, the world’s most popular front-end
            open source toolkit, featuring Sass variables and
            mixins, responsive grid system, extensive prebuilt
            components, and powerful JavaScript plugins.
          </p>
          <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <a href="/temp-records/create" class="btn btn-primary">New</a>

          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Model</th>
                <th>Equipment</th>
                <th>In-Charge</th>
                <th>Checked By</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in records.data" :key="r.id">
                <td>{{ r.id }}</td>
                <td>{{ r.date }}</td>
                <td>{{ r.model_series }}</td>
                <td>{{ r.equipment_type }}</td>
                <td>{{ r.person_in_charge }}</td>
                <td>{{ r.checked_by }}</td>
                <td class="text-end">
                  <div class="dropdown dropup d-inline-block position-static">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                      data-bs-toggle="dropdown" data-bs-display="static" data-bs-boundary="viewport"
                      aria-expanded="false">
                      Actions
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <a class="dropdown-item" :href="`/temp-records/${r.id}`">View</a>
                      </li>
                      <li>
                        <a class="dropdown-item" :href="`/temp-records/${r.id}/edit`">Edit</a>
                      </li>
                      <li>
                        <hr class="dropdown-divider" />
                      </li>
                      <li>
                        <button class="dropdown-item text-danger" @click.prevent="
                          destroy(r.id)
                          ">
                          Delete
                        </button>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer">
        <nav v-if="records.links && records.links.length">
          <ul class="pagination mb-0">
            <li v-for="(l, idx) in records.links" :key="idx" class="page-item"
              :class="{ active: l.active, disabled: !l.url }">
              <a class="page-link" :href="l.url || '#'" v-html="l.label"></a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({ records: Object });
import { router } from "@inertiajs/vue3";

function destroy(id) {
  if (!confirm("Delete this record?")) return;
  const csrf =
    document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute("content") || "";
  router.post(
    `/temp-records/${id}`,
    { _method: "delete", _token: csrf },
    { preserveScroll: true }
  );
}
</script>
