<template>
  <n-space vertical size="large" style="width: 100%; padding-top: 20px;">
    <n-form model-value="formValue" ref="formRef" label-placement="left">
      <n-grid :cols="2">
        <n-grid-item style="padding-right: 10px;">
          <n-scrollbar style="max-height: 300px;">
            <draggable v-model="formValue.availableGroups" class="list-group" group="shared" item-key="id">
              <template #item="{ element }">
                <div class="list-group-item" :key="element.id">
                  {{ element.title }}
                </div>
              </template>
            </draggable>
          </n-scrollbar>
        </n-grid-item>
        <n-grid-item style="padding-left: 10px;">
          <n-scrollbar style="max-height: 300px;">
            <draggable v-model="formValue.selectedGroups" class="list-group" group="shared" item-key="id">
              <template #item="{ element }">
                <div class="list-group-item" :key="element.id">
                  {{ element.title }}
                </div>
              </template>
            </draggable>
          </n-scrollbar>
        </n-grid-item>
      </n-grid>
      <n-grid :cols="2" style="padding-top: 20px;">
        <n-grid-item style="padding-right: 10px;">
          <n-form-item label="Mailing List Name">
            <n-input v-model="formValue.groupName" placeholder="Enter mailing list name"/>
          </n-form-item>
        </n-grid-item>
      </n-grid>
      <n-grid :cols="1" style="padding-top: 20px;">
        <n-grid-item>
          <n-space align="center">
            <n-button type="primary" @click="saveGroup">Save</n-button>
            <n-button @click="cancelGroupEdit">Cancel</n-button>
          </n-space>
        </n-grid-item>
      </n-grid>
    </n-form>
  </n-space>
</template>

<script>
import {onMounted, reactive, ref} from 'vue';
import {
  NButton,
  NForm,
  NFormItem,
  NGrid,
  NGridItem,
  NInput,
  NScrollbar,
  NSpace,
  useLoadingBar,
  useMessage
} from 'naive-ui';
import draggable from 'vuedraggable';
import {useUserGroupStore} from "../stores/userGroupStore";
import {useMailingListStore} from "../stores/mailinglistStore";

export default {
  name: 'GroupEditorDialog',
  components: {
    NForm,
    NFormItem,
    NInput,
    NSpace,
    NButton,
    NGrid,
    NGridItem,
    NScrollbar,
    draggable
  },
  props: {
    id: {
      type: Number,
      default: 0,
      required: false
    }
  },
  setup(props) {
    const formRef = ref(null);
    const formValue = ref({
      groupName: '',
      availableGroups: [],
      selectedGroups: []
    });
    const userGroupStore = useUserGroupStore();
    const mailingListStore = useMailingListStore();
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar()
    const state = reactive({
      mailingListMode: ''
    });
    const pagination = reactive({
      page: 1,
      pageSize: 5,
      pageCount: 1,
      itemCount: 0,
      size: 'large'
    });

    onMounted(async (id) => {
      await userGroupStore.getList(msgPopup, loadingBar);
      if (props.id) {
        alert(id)
        const entryDetails = await mailingListStore.fetchEntryDetails(props.id, msgPopup, loadingBar);
      }
      formValue.value.availableGroups = userGroupStore.documentsPage.docs;
    });

    const saveGroup = () => {

      const selectedGroupsValidation = rules.selectedGroups.validator(null, formValue.value.selectedGroups);
      if (!selectedGroupsValidation) {
        msgPopup.error(rules.selectedGroups.message, {
          closable: true,
          duration: 10000
        });
        return;
      }

      formRef.value.validate((errors) => {
        if (!errors) {
          userGroupStore.saveList(formRef.value, '', msgPopup, loadingBar).then(() => {
            state.mailingListMode = '';
            formValue.value.groupName = '';
            formValue.value.selectedGroups = [];
          }).catch((e) => {
            msgPopup.error(e, {
              closable: true,
              duration: 10000
            });
          });
        } else {
          Object.keys(errors).forEach(fieldName => {
            const fieldError = errors[fieldName];
            if (fieldError && fieldError.length > 0) {
              msgPopup.warning(fieldError[0].message, {
                closable: true,
                duration: 10000
              });
            }
          });
        }
      });
    };

    const cancelGroupEdit = () => {
      formValue.value.groupName = '';
      formValue.value.selectedGroups = [];
      formValue.value.availableGroups = userGroupStore.documentsPage.docs;
      state.mailingListMode = '';
    };

    const rules = {
      groupName: {
        required: true,
        message: 'Mailing list name cannot be empty'
      },
      selectedGroups: {
        validator(rule, value) {
          return value && value.length > 0;
        },
        message: 'At least one group should be selected'
      }
    };

    return {
      formValue,
      formRef,
      saveGroup,
      cancelGroupEdit,
      pagination

    };
  }
};
</script>
