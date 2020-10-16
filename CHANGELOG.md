# 6.3.0

- Marked `transition.schema` as deprecated.
- Added expanding by `state.transitions`.
- Added expanding by `sample.states`.
- Added expanding by `schema`.
  - This can be used for expanding states, transitions, etc.

# 6.2.0

- Added expanding by `transition.state_from`.
- Added expanding by `transition.state_to`.
- Added expanding by `transition.schema`.
- Added expanding by `transition.dispatchers.

# 6.1.1

- `AfterTransiitonIndex` accepts `conditions` parameter now.
  - Set `conditions` to `false` to skip conditions.

# 6.1.0

- `AfterTransiitonIndex` plugin added.
  - Checks conditions for each transition: skips transition if condition failed.

# 6.0.2

- Version to operations added.
- Actualized tests.

# 6.0.1

- Operations configuration in the extas.json fixed.

# 6.0.0

- `extas-jsonrpc` dep removed.
- Use `extas-api-jsonrpc`.

# 5.0.0

- Update to `extas workflow` `4.*`.