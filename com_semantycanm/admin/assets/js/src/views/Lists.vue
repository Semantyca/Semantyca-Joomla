<template>
  <div class="container">

    <div class="row">
      <div class="col-md-6">
        <h3>{{ store.translations.AVAILABLE_USER_GROUPS }}</h3>
        <div class="col-md-12 dragdrop-list">
          <ul ref="availableGroupsRef"
              class="list-group"
              id="availableGroups">
            <li v-for="entry in userGroupStore.documentsPage.docs"
                :key="entry.id"
                class="list-group-item"
                :id="entry.id">{{ entry.title }}
            </li>
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <h3>{{ store.translations.SELECTED_USER_GROUPS }}</h3>
        <div class="col-md-12 dragdrop-list">
          <ul ref="selectedGroupsRef"
              id="selectedGroups"
              class="list-group">
          </ul>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-md-12">
        <form class="row needs-validation" novalidate>
          <div class="input-group gap-2">
            <div class="col-md-3">
              <input type="text" class="form-control" id="mailingListName"
                     placeholder="Mailing List Name" required>
              <div class="invalid-feedback">
                {{ store.translations.VALIDATION_EMPTY_MAILING_LIST }}
              </div>
            </div>
            <div class="col-md-3">
              <button id="saveGroup" class="btn btn-success" @click="saveList">{{ store.translations.SAVE }}</button>
              <button id="cancelEditing" class="btn btn-secondary" @click="cancelList">{{
                  store.translations.CANCEL
                }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="row mt-4">
      <div class="header-container d-flex justify-content-between align-items-center">
        <h3>{{ store.translations.MAILING_LISTS }}</h3>
      </div>
      <div>
        <n-data-table
            :columns="columns"
            :data="store.mailingListDocsView.docs"
            :pagination="pagination"
        />
        <label for="mailingListMode"></label>
        <input type="hidden" id="mailingListMode" value=""/>
      </div>
    </div>
  </div>
</template>

<script>
import {nextTick, onMounted, reactive, ref} from 'vue';
import {useGlobalStore} from "../stores/globalStore";
import {NDataTable} from "naive-ui";
import {useUserGroupStore} from "../stores/userGroupStore";

export default {
  name: 'Lists',
  components: {
    NDataTable,
  },
  setup() {
    const availableGroupsRef = ref(null);
    const selectedGroupsRef = ref(null);
    const store = useGlobalStore();
    const userGroupStore = useUserGroupStore();
    const saveList = () => {
      const mailingListName = document.getElementById('mailingListName').value;
      const listItems = Array.from(document.querySelectorAll('#selectedGroups li')).map(li => li.id);
      if (mailingListName === '') {
        showWarnBar("Mailing list name cannot be empty");
      } else if (listItems.length === 0) {
        showWarnBar('The list is empty');
      } else {
        let mode = document.getElementById('mailingListMode').value;
        const mailingListRequest = new MailingListRequest(mode);
        mailingListRequest.process(mailingListName, listItems);
      }
    };

    const cancelList = () => {
      let mode = document.getElementById('mailingListMode');
      if (mode.value !== '') {
        const tableRows = document.querySelectorAll('#mailingList tr');
        tableRows.forEach(tr => {
          tr.style.opacity = '1';
          tr.style.pointerEvents = 'auto';
        });
        document.getElementById('selectedGroups').innerHTML = '';
        mode.value = '';
      }
      document.getElementById('mailingListName').value = '';
    };

    onMounted(async () => {
      await store.refreshMailingList(1);
      await userGroupStore.getList();
      await nextTick(() => {
        applyAndDropSet([availableGroupsRef.value, selectedGroupsRef.value]);
      });
    });

    const applyAndDropSet = (lists) => {
      lists.forEach(list => {
        Sortable.create(list, {
          group: {
            name: 'shared',
            pull: true,
            put: true
          },
          animation: 150,
          sort: false
        });
      });
    };

    const createColumns = () => {
      return [
        {
          title: 'Name',
          key: 'name'
        },
        {
          title: 'Created',
          key: 'reg_date'
        }
      ]
    }

    const paginationReactive = reactive({
      page: 1,
      pageSize: 10,
      onChange: (page) => {
        paginationReactive.page = page;
      },
      onUpdatePageSize: (pageSize) => {
        paginationReactive.pageSize = pageSize;
        paginationReactive.page = 1;
      }
    });

    return {
      userGroupStore,
      store,
      availableGroupsRef,
      selectedGroupsRef,
      saveList,
      cancelList,
      columns: createColumns(),
      pagination: paginationReactive,
    };
  }
};
</script>
