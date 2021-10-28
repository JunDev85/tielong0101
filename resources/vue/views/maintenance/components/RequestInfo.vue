<template>
  <el-card class="box-card">
    <div slot="header">
      <span>依頼情報</span>

      <el-button type="primary" size="small" @click="editVisible = true"
        >編集</el-button
      >
    </div>

    <br />
    <el-row :gutter="10">
      <el-col :span="10">
        <table class="detail-table">
          <tbody>
            <tr>
              <th style="line-height: 3">メンテナンスNo</th>
              <td>{{ detail.maintenance_code }}</td>
            </tr>
          </tbody>
        </table>
      </el-col>
      <el-col :span="10">
        <table class="detail-table">
          <tbody>
            <tr>
              <th>店舗CD</th>
              <td style="text-align: center">{{ detail.shop.shop_id }}</td>
              <td style="text-align: center">
                {{ detail.shop.business_category.business_category }}
              </td>
            </tr>
            <tr>
              <th>店舗名</th>
              <td colspan="2" style="text-align: center">
                {{ detail.shop.shop_name }}
              </td>
            </tr>
          </tbody>
        </table>
      </el-col>
      <el-col :span="4">
        <el-button
          type="info"
          size="mini"
          width="120px"
          @click="baseInfoVisible = true"
          >基本情報</el-button
        >
      </el-col>
    </el-row>
    <el-divider />
    <el-row :gutter="10">
      <el-col :span="10">
        <table class="detail-table">
          <tbody>
            <tr>
              <th>大分類*</th>
              <td>
                {{ detail.category ? detail.category.category_name : '' }}
              </td>
            </tr>
            <tr>
              <th>中分類*</th>
              <td>
                {{
                  detail.sub_category
                    ? detail.sub_category.sub_category_name
                    : ''
                }}
              </td>
            </tr>
          </tbody>
        </table>
        <table class="detail-table">
          <tbody>
            <tr>
              <th>依頼区分*</th>
              <td>
                <span
                  v-if="detail.order_type.order_type_id < 4"
                  >{{ order_text }}</span>

                <span style="padding-right: 10px"
                  v-if="detail.order_type.order_type_id > 3"
                  >{{ detail.order_type.type }}</span>
                <el-tooltip
                v-if="detail.order_type.order_type_id > 3"
                  class="item"
                  effect="dark"
                  :content="otherText()"
                  placement="top"
                  style="
                    background-color: transparant;
                    border: 0;
                  "
                >
                  <svg-icon icon-class="comment" />
                </el-tooltip>
              </td>
            </tr>
          </tbody>
        </table>
      </el-col>
      <el-col :span="14">
        <table class="detail-table">
          <tbody>
            <tr>
              <th>取引先コード</th>
              <td v-if="detail.customer_code">
                {{ detail.customer_code }}
              </td>
              <td v-else style="width: 150px"></td>
              <td style="border: none; padding: 0 5px; text-align: right">
                <el-button
                  type="info"
                  size="mini"
                  width="120px"
                  @click="createMailVisible = true"
                  >取引先メール</el-button
                >
              </td>
            </tr>
          </tbody>
        </table>
        <table class="detail-table">
          <tbody>
            <tr>
              <th>取引先名</th>
              <td colspan="2">
                {{
                  detail.customerInformation[
                    detail.customerInformation.length - 1
                  ].customer_name
                }}
              </td>
            </tr>
            <tr>
              <th>TEL/FAX</th>
              <td>
                {{
                  detail.customerInformation[
                    detail.customerInformation.length - 1
                  ].TEL
                }}
              </td>
              <td>
                {{
                  detail.customerInformation[
                    detail.customerInformation.length - 1
                  ].FAX
                }}
              </td>
            </tr>
          </tbody>
        </table>
      </el-col>
    </el-row>
    <table class="detail-table">
      <tbody>
        <tr>
          <th>依頼内容*</th>
          <td>{{ detail.order }}</td>
          <td rowspan="3" style="padding: 0">
            <p class="header-1" style="margin-top: 0; padding: 10px">
              添付ファイル
            </p>
            <div        
              style="
                text-align: center;
                padding: 5px;
                max-height: 300px;
                overflow-x: hidden;
                overflow-y: auto;
              "
            >
            <span v-for="(item, index) in detail.maintenance_images"
              :key="index"      >
              <el-image v-if="index == 0"
                style="
                  width: 100px;
                  height: 100px;
                  display: block;
                  padding: 10px;
                "
                :src="getsrc(detail.maintenance_images[0].file_name)"
                :preview-src-list="fileList"
              />
              <a href="#" style="margin-bottom: 10px; display: block" v-if="index == 0"
                >全体写真</a
              >
              <el-image v-if="index == 0"
                style="
                  width: 100px;
                  height: 100px;
                  display: block;
                  padding: 10px;
                "
                :src="getsrc(detail.maintenance_images[1].file_name)"
                :preview-src-list="fileList"
              />
              <a href="#" style="margin-bottom: 10px; display: block" v-if="index == 0"
                >細部写真</a
              >
              <el-image v-if="index == 0"
                style="
                  width: 100px;
                  height: 100px;
                  display: block;
                  padding: 10px;
                "
                :src="getsrc(detail.maintenance_images[2].file_name)"
                :preview-src-list="fileList"
              />
              <a href="#" style="margin-bottom: 10px; display: block" v-if="index == 0"
                >型番写真</a
              >
              <el-image v-if="index > 2"
                style="
                  width: 100px;
                  height: 100px;
                  display: block;
                  padding: 10px;
                "
                :src="getsrc(detail.maintenance_images[index].file_name)"
                :preview-src-list="fileList"
              />              
            </span>
            </div>
          </td>
        </tr>
        <tr>
          <th>初期対応*</th>
          <td>{{ detail.first_handling }}</td>
        </tr>
        <tr>
          <th>備考</th>
          <td>{{ detail.remark }}</td>
        </tr>
      </tbody>
    </table>
    <table class="detail-table">
      <tbody>
        <tr>
          <th rowspan="5">案件情報</th>
          <th>
            {{
              detail.maintenance_matters[0].matter_option
                ? detail.maintenance_matters[0].matter_option.name
                : ''
            }}
          </th>
          <td style="min-width: 100px">
            {{
              detail.maintenance_matters[0].matter_value
                ? detail.maintenance_matters[0].matter_value.name
                : ''
            }}
          </td>
          <th>
            {{
              detail.maintenance_matters[5].matter_option
                ? detail.maintenance_matters[5].matter_option.name
                : ''
            }}
          </th>
          <td style="min-width: 100px">
            {{
              detail.maintenance_matters[5].matter_value
                ? detail.maintenance_matters[5].matter_value.name
                : ''
            }}
          </td>
        </tr>
        <tr>
          <th>
            {{
              detail.maintenance_matters[1].matter_option
                ? detail.maintenance_matters[1].matter_option.name
                : ''
            }}
          </th>
          <td>
            {{
              detail.maintenance_matters[1].matter_value
                ? detail.maintenance_matters[1].matter_value.name
                : ''
            }}
          </td>
          <th>
            {{
              detail.maintenance_matters[6].matter_option
                ? detail.maintenance_matters[6].matter_option.name
                : ''
            }}
          </th>
          <td>
            {{
              detail.maintenance_matters[6].matter_value
                ? detail.maintenance_matters[6].matter_value.name
                : ''
            }}
          </td>
        </tr>
        <tr>
          <th>
            {{
              detail.maintenance_matters[2].matter_option
                ? detail.maintenance_matters[2].matter_option.name
                : ''
            }}
          </th>
          <td>
            {{
              detail.maintenance_matters[2].matter_value
                ? detail.maintenance_matters[2].matter_value.name
                : ''
            }}
          </td>
          <th>
            {{
              detail.maintenance_matters[7].matter_option
                ? detail.maintenance_matters[7].matter_option.name
                : ''
            }}
          </th>
          <td>
            {{
              detail.maintenance_matters[7].matter_value
                ? detail.maintenance_matters[7].matter_value.name
                : ''
            }}
          </td>
        </tr>
        <tr>
          <th>
            {{
              detail.maintenance_matters[3].matter_option
                ? detail.maintenance_matters[3].matter_option.name
                : ''
            }}
          </th>
          <td>
            {{
              detail.maintenance_matters[3].matter_value
                ? detail.maintenance_matters[3].matter_value.name
                : ''
            }}
          </td>
          <th>
            {{
              detail.maintenance_matters[8].matter_option
                ? detail.maintenance_matters[8].matter_option.name
                : ''
            }}
          </th>
          <td>
            {{
              detail.maintenance_matters[8].matter_value
                ? detail.maintenance_matters[8].matter_value.name
                : ''
            }}
          </td>
        </tr>
        <tr>
          <th>
            {{
              detail.maintenance_matters[4].matter_option
                ? detail.maintenance_matters[4].matter_option.name
                : ''
            }}
          </th>
          <td>
            {{
              detail.maintenance_matters[4].matter_value
                ? detail.maintenance_matters[4].matter_value.name
                : ''
            }}
          </td>
          <th>
            {{
              detail.maintenance_matters[9].matter_option
                ? detail.maintenance_matters[9].matter_option.name
                : ''
            }}
          </th>
          <td>
            {{
              detail.maintenance_matters[9].matter_value
                ? detail.maintenance_matters[9].matter_value.name
                : ''
            }}
          </td>
        </tr>
      </tbody>
    </table>
    <el-dialog
      title="【取引先メール作成】"
      :visible.sync="createMailVisible"
      :width="mailDialogWidth"
    >
      <create-client-mail />
      <span slot="footer" class="dialog-footer">
        <span>宛先とCC宛先、それぞれ選択してください。</span>
        <a
          target="_blank"
          href="mailto:name@email.com?cc=name1@email.com;name2@mail.com"
          >Link text</a
        >
        <el-button type="primary" @click="createMailVisible = false"
          >メーラー起動</el-button
        >
        <el-button @click="createMailVisible = false">閉じる</el-button>
      </span>
    </el-dialog>

    <el-dialog title="【依頼内容】" :visible.sync="otherinfo" :width="otherdialogWidth">
      <span>{{ detail.order_type_other_text }}</span>
    </el-dialog>


    <el-dialog
      title=""
      :visible.sync="baseInfoVisible"
      :width="baseinfoWidth"
      top="0px"
      custom-class="slide-dialog"
    >
      <base-info :detail="detail" />
    </el-dialog>

    <el-dialog
      title="依頼情報 編集"
      :visible.sync="editVisible"
      :width="editdialogWidth"
      custom-class="slide-dialog"
      top="0px"
    >
      <request-info-edit ref="editForm" :detail="detail" />
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="save()">登録</el-button>
        <el-button @click="closeRequestEdit">閉じる</el-button>
      </span>
    </el-dialog>
  </el-card>
</template>

<script>
import MaintenanceResource from '@/api/maintenance';
import CreateClientMail from './sub/CreateClientMail.vue';
import CreateSetting from './sub/CreateSetting.vue';
import BaseInfo from './BaseInfo.vue';
import RequestInfoEdit from './sub/RequestInfoEdit.vue';

const resource = new MaintenanceResource();

export default {
  components: { CreateClientMail, BaseInfo, RequestInfoEdit, CreateSetting },
  props: {
    selected: '',
    detail: {
      type: Object,
      default: () => {
        return {};
      },
    },
  },
  data() {
    return {
      baseinfoWidth: '700px',
      editdialogWidth: '60%',
      otherdialogWidth: '45%',
      mailDialogWidth: '45%',
      dataa: null,
      show: true,
      order_text: '',
      firstimage: '',
      customer_name: '',
      secondimage: '',
      thirdimage: '',
      createMailVisible: false,
      setting: false,
      baseInfoVisible: false,
      editVisible: false,
      otherinfo: false,
      visibleflag: true,
      fileList: [],
      order_content: [
        '修理をしてほしい',
        '部品を送ってほしい',
        '調査してほしい',
        '調査してほしい',
      ],

      tableData: [
        { title: '特記①', value: '20’10/13〜10/25改装 浄化槽店舗' },
        {
          title: '特記②',
          value: '20’5/21エアコン全台清掃 駐車場案件は大和ハウス様へ',
        },
      ],
    };
  },
  mounted() {
    const dialogs = document.querySelectorAll('.slide-dialog');
    dialogs.forEach((el) => {
      el.closest('.el-dialog__wrapper').classList.add('slide-dialog-wrapper');
    });

    this.orderText();

    if(this.isMobile()) {
      this.otherdialogWidth = '100%';
      this.baseinfoWidth = '100%';
      this.editdialogWidth = '100%';
      this.mailDialogWidth = '100%';
    }

    this.imagesListCal();

  },
  created() {

  },
  methods: {
    closeRequestEdit() {
      this.editVisible = false;
      document.querySelector("#app > div > div.main-container > section > div > div.el-row > div:nth-child(1) > div > div.el-card__body > div:nth-child(10)").click();
    },

    isMobile() {
      var check = true;
      if(document.querySelector("body").clientWidth > 737) check = false;
      return check;
    },

    otherText() {

      var text = "";

      if(this.detail.order_type_id > 3 && this.detail.order_type.type != ''){
        text = this.detail.order_type_other_text;
      }
      return text;
    },

    imagesListCal() {
      for(var i = 0; i < this.detail.maintenance_images.length; i ++) {
        this.fileList.push('./maintenance/image/' + this.detail.maintenance_id + '/' + this.detail.maintenance_images[i].file_name);   
      }
    },

    orderText() {
      if (
        this.detail.order_type.order_type_id < 4 &&
        this.detail.order_type.type != ''
      ) {
        var type = this.detail.order_type.type,
          flag = 0;
        var reasons = this.detail.order_reasons;
        if (reasons.length > 0) {
          reasons.forEach((el) => {
            if (flag > 0) {
              type += ',' + el.reason;
            } else {
              type += '(' + el.reason;
              flag++;
            }
          });
          type += ')';
        }
        this.order_text = type;
      } else if (
        this.detail.order_type_id > 3 &&
        this.detail.order_type.type != ''
      ) {
        var type = this.detail.order_type.type;
        this.order_text = type;
      } else {
        return;
      }
    },

    async save() {
      this.editVisible = false;
      await this.$refs.editForm.save();
      this.$emit('get-detail');
    },
    getsrc(firstimage) {
      // return;
      const fsrc =
        './maintenance/image/' +
        this.detail.maintenance_id +
        '/' +
        firstimage;
      return fsrc;
    },
    getImageone() {
      const file_data = {
        file_name: this.detail.maintenance_images[0].file_name,
      };

      resource
        .getImage(this.detail.maintenance_id, file_data)
        .then((res) => {});
    },
  },
};
</script  >

<style lang="scss" scoped>
</style>