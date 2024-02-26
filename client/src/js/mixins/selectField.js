import qs from 'qs';
import axios from 'axios';
import VueSimpleSuggest from 'vue-simple-suggest/dist/cjs';
import * as locales from 'src/lang';

export default {
  props: {
    payload: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      isLoading: false,
    };
  },
  created() {
    this.setLocale();
  },
  components: { VueSimpleSuggest },
  computed: {
    cleanTerm() {
      return this.term && typeof this.term === 'string' ? this.term.trim() : '';
    },
    endpoint() {
      return this.payload.config.searchEndpoint;
    },
    endpointWithParams() {
      let params = {};

      if (this.payload.config.getVars && typeof this.payload.config.getVars === 'object') {
        params = {
          ...this.payload.config.getVars
        };
      }

      params.query = this.cleanTerm;

      return `${this.endpoint}?${qs.stringify(params, { encode: true })}`;
    },
    searchAxiosConfig() {
      const config = {};

      if (this.payload.config.headers && typeof this.payload.config.headers === 'object') {
        config.headers = {
          ...this.payload.config.headers
        };
      }

      return config;
    },
  },
  methods: {
    setLocale() {
      const locale = this.payload.lang ?? 'en';

      if (this.$i18n) {
        this.$i18n.setLocaleMessage(locale, locales[locale]);
        this.$i18n.locale = locale;
      }
    },
    async suggest() {
      this.isLoading = true;
      const result = await new Promise((resolve) => {
        if (this.cleanTerm.length < this.payload.config.minSearchChars) {
          resolve([]);
        } else {
          axios
            .get(this.endpointWithParams, this.searchAxiosConfig)
            .then((response) => {
              resolve(response.data);
            });
        }
      });
      this.isLoading = false;

      return result;
    }
  }
};
