import { AbilityBuilder, createMongoAbility } from '@casl/ability';

export default function defineAbilitiesFor(user) {
    const { can, cannot, build } = new AbilityBuilder(createMongoAbility);

    // Define guest abilities
    can('read', 'Dashboard');

    // If user is logged in
    if (user) {
        // Basic authenticated user permissions
        can('viewAny', 'Dashboard');
        can('view', 'Profile', { id: user.id });

        // Admin permissions
        if (user.is_admin) {
            can('manage', 'all');
        }
    }

    return build();
}

// Export a default ability instance for initial state
export const initialAbility = createMongoAbility();