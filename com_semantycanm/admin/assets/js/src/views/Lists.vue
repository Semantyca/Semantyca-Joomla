<template>
  <div class="container mt-3">
    <n-grid :cols="24">
      <n-grid-item :span="11">
        <h3>{{ store.translations.AVAILABLE_USER_GROUPS }}</h3>
        <div class="dragdrop-list">
          <draggable v-model="state.availableGroups" class="list-group" group="shared" itemKey="id">
            <template #item="{ element }">
              <div class="list-group-item" :key="element.id">
                {{ element.title }}
              </div>
            </template>
          </draggable>
        </div>
      </n-grid-item>
      <n-grid-item :span="2">
      </n-grid-item>
      <n-grid-item :span="11">
        <h3>{{ store.translations.SELECTED_USER_GROUPS }}</h3>
        <div class="dragdrop-list">
          <draggable v-model="state.selectedGroups" class="list-group" group="shared" itemKey="id">
            <template #item="{ element }">
              <div class="list-group-item" :key="element.id">
                {{ element.title }}
              </div>
            </template>
          </draggable>
        </div>
      </n-grid-item>
    </n-grid>
    <div class="mt-3">
      <n-form
          inline
          ref="formRef"
          :rules="rules"
          :model="formValue">
        <n-grid :cols="24" x-gap="12">
          <n-grid-item :span="14">
            <n-form-item label="Mailing List Name" path="groupName">
              <n-input v-model:value="state.groupName"
                       id="mailingListName"
                       type="text"
                       placeholder="Mailing List Name"/>
            </n-form-item>
          </n-grid-item>
          <n-grid-item :span="10">
            <n-space>
              <n-button id="saveGroup"
                        type="primary"
                        @click="validateAndSave">{{ store.translations.SAVE }}
              </n-button>
              <n-button id="cancelEditing"
                        type="success"
                        @click="cancelList">{{ store.translations.CANCEL }}
              </n-button>
            </n-space>
          </n-grid-item>
        </n-grid>
      </n-form>
    </div>
    <n-grid :cols="24" class="mt-4">
      <n-grid-item :span="24">
        <div class="header-container d-flex justify-content-between align-items-center">
          <h3>{{ store.translations.MAILING_LISTS }}</h3>
        </div>
      </n-grid-item>
      <n-grid-item :span="24">
        <n-data-table
            :columns="columns"
            :data="mailingListStore.mailingListDocsView.docs"
            :pagination="pagination"
        />
        <input type="hidden" id="mailingListMode" value=""/>
      </n-grid-item>
    </n-grid>
  </div>
</template>

<script>
import {onMounted, reactive, ref} from 'vue';
import {NButton, NDataTable, NForm, NFormItem, NGrid, NGridItem, NInput, NSpace} from "naive-ui";
import {useUserGroupStore} from "../stores/userGroupStore";
import {useMailingListStore} from "../stores/mailinglistStore";
import {useGlobalStore} from "../stores/globalStore";
import draggable from 'vuedraggable';

export default {
  name: 'Lists',
  components: {
    NGrid,
    NGridItem,
    NButton,
    NSpace,
    NInput,
    NDataTable,
    NForm,
    NFormItem,
    draggable
  },
  setup() {
    const formRef = ref(null);
    const store = useGlobalStore();
    const userGroupStore = useUserGroupStore();
    const mailingListStore = useMailingListStore();

    const state = reactive({
      mailingListMode: '',
      groupName: '',
      availableGroups: [],
      selectedGroups: []
    });

    const validateAndSave = (e) => {
      e.preventDefault();
      console.log(formRef.value);
      //    nextTick(() => {
      if (formRef.value) {
        formRef.value.validate((errors) => {
          if (!errors) {
            userGroupStore.saveList(state);
          } else {
            console.log('Validation Failed', errors);
            // Handle validation failure (show messages, etc.)
          }
        });
      }
      //    });
    };

    const cancelList = () => {
      state.selectedGroups = [];
      state.availableGroups = userGroupStore.documentsPage.docs;
      state.mailingListMode = '';
      state.groupName = '';
    };

    onMounted(async () => {
      console.log(formRef.value);
      await userGroupStore.getList();
      await mailingListStore.refreshMailingList(1);
      state.availableGroups = userGroupStore.documentsPage.docs;
    });

    const rules = {
      groupName: [
        {
          required: true,
          message: 'Mailing list name cannot be empty',
          trigger: ['input']
        }
      ]
    };

    const formValue = ref({
      groupName: undefined
    });

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
      pageSize: 5,
      onChange: (page) => {
        paginationReactive.page = page;
      },
      onUpdatePageSize: (pageSize) => {
        paginationReactive.pageSize = pageSize;
        paginationReactive.page = 1;
      }
    });

    return {
      state,
      store,
      userGroupStore,
      mailingListStore,
      cancelList,
      validateAndSave,
      rules,
      columns: createColumns(),
      pagination: paginationReactive,
      formValue
    };
  }
};
</script>
