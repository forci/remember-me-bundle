# remember-me-bundle

Persistent Remember Me bundle for Symfony applications

- If you'd like to use a custom Token class, extend it, pass the config and DO NOT include the bundle in the auto-mapping list of bundles

# TODO

- Documentation
- Tests
- Make it possible to attach the bundle to another Doctrine connection via a compiler pass that aliases `doctrine.orm.%manager_name%_entity_manager` to `forci_remember_me.entity_manager`
```xml
<service id="forci_remember_me.repo.abstract" abstract="true">
    <factory service="doctrine.orm.default_entity_manager" method="getRepository"/>
</service>
```