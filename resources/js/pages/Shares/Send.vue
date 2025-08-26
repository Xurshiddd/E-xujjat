<script>
export default {
  props: {
    shares: Array
  }
}
</script>

<template>
  <table>
    <thead>
      <tr>
        <th>Type</th><th>URL</th><th>Users</th><th>Expires At</th><th>Password</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(share, index) in shares" :key="index">
        <td>{{ share.type }}</td>
        <td><a :href="share.url" target="_blank">{{ share.url }}</a></td>
        <td>{{ share.users }}</td>
        <td>{{ share.expires_at }}</td>
        <td>{{ share.password }}</td>
      </tr>
    </tbody>
  </table>
</template>
