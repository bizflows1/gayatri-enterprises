import { useEffect, useState } from "react";
import { motion } from "motion/react";
import { Linkedin, Mail } from "lucide-react";
import { api } from "../lib/api";

interface TeamMember {
  id: number;
  name: string;
  role: string;
  bio?: string | null;
  image?: string | null;
  category: "head" | "staff" | "partner" | "associate";
}

const STATIC_HEADS: TeamMember[] = [
  {
    id: 0,
    name: "Rakesh Sharma",
    role: "Managing Director",
    image: "/images/member_md.jpeg",
    bio: "Over 25 years of experience in chemical procurement and supply chain management.",
    category: "head",
  },
  {
    id: 0,
    name: "Dr. Anjali Desai",
    role: "Technical Lead",
    image: "/images/member_tech.jpeg",
    bio: "Ph.D. in Analytical Chemistry, ensuring quality control and client technical support.",
    category: "head",
  },
];

const STATIC_STAFF: TeamMember[] = [
  { id: 0, name: "Vikram Singh",  role: "Head of Logistics",          image: "/images/member_logistics.jpeg", category: "staff" },
  { id: 0, name: "Priya Patel",   role: "Client Relations Manager",   image: "/images/member_relations.jpeg", category: "staff" },
  { id: 0, name: "Arun Kumar",    role: "Procurement Specialist",     image: "/images/member_procure.jpeg",   category: "staff" },
  { id: 0, name: "Meena Reddy",  role: "Quality Assurance Officer",   image: "/images/member_qa.jpeg",        category: "staff" },
];

function useTeam() {
  const [heads, setHeads] = useState<TeamMember[]>(STATIC_HEADS);
  const [staff, setStaff] = useState<TeamMember[]>(STATIC_STAFF);

  useEffect(() => {
    let cancelled = false;
    api.get<{ members: TeamMember[] }>("/api/team")
      .then((data) => {
        if (cancelled || !data.members?.length) return;
        const apiHeads = data.members.filter((m) => m.category === "head" || m.category === "partner");
        const apiStaff = data.members.filter((m) => m.category === "staff" || m.category === "associate");
        if (apiHeads.length) setHeads(apiHeads);
        if (apiStaff.length) setStaff(apiStaff);
      })
      .catch(() => {});
    return () => { cancelled = true; };
  }, []);

  return { heads, staff };
}

export default function Team() {
  const { heads, staff } = useTeam();

  return (
    <div className="bg-white min-h-screen pt-32 pb-24">
      <div className="max-w-[90rem] mx-auto px-6 md:px-12">
        <motion.div
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8 }}
          className="mb-20 max-w-3xl"
        >
          <div className="flex items-center gap-4 text-xs font-mono text-emerald uppercase tracking-widest mb-12">
            <div className="w-8 h-[1px] bg-emerald"></div>
            <span>OUR LEADERSHIP</span>
          </div>
          <h1 className="text-5xl md:text-7xl font-heading font-medium text-navy mb-8 tracking-tight leading-[0.9]">
            The people behind<br />every shipment.
          </h1>
          <p className="text-xl text-slate font-light">
            Meet the experienced professionals ensuring precision, quality, and reliability in every order.
          </p>
        </motion.div>

        {/* Leadership — featured, larger */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 mb-24">
          {heads.map((member, idx) => (
            <motion.div
              key={member.id || member.name}
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-50px" }}
              transition={{ duration: 0.5, delay: idx * 0.05 }}
              className="group bg-soft-bg rounded-2xl overflow-hidden border border-border hover:shadow-lg transition-all duration-300"
            >
              <div className="aspect-[16/10] relative overflow-hidden">
                {member.image ? (
                  <img
                    src={member.image}
                    alt={member.name}
                    className="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                    loading="lazy"
                  />
                ) : (
                  <div className="absolute inset-0 bg-navy/10 flex items-center justify-center">
                    <span className="text-4xl font-heading font-medium text-navy/30">{member.name[0]}</span>
                  </div>
                )}
              </div>
              <div className="p-8 md:p-10">
                <h3 className="text-2xl font-heading font-medium text-navy mb-1">{member.name}</h3>
                <p className="text-emerald font-medium mb-4 text-xs uppercase tracking-widest">{member.role}</p>
                {member.bio && (
                  <p className="text-slate mb-6 leading-relaxed text-lg">{member.bio}</p>
                )}
                <div className="flex space-x-4 pt-4 border-t border-border">
                  <button className="text-slate/50 hover:text-navy transition-colors" aria-label={`${member.name} on LinkedIn`}>
                    <Linkedin className="w-5 h-5" />
                  </button>
                  <button className="text-slate/50 hover:text-navy transition-colors" aria-label={`Email ${member.name}`}>
                    <Mail className="w-5 h-5" />
                  </button>
                </div>
              </div>
            </motion.div>
          ))}
        </div>

        {/* Staff — compact directory */}
        {staff.length > 0 && (
          <div className="thin-border-t pt-16">
            <h2 className="text-xs font-mono uppercase tracking-widest text-slate-500 mb-10">Team</h2>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
              {staff.map((member, idx) => (
                <motion.div
                  key={member.id || member.name}
                  initial={{ opacity: 0, y: 16 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true, margin: "-50px" }}
                  transition={{ duration: 0.4, delay: idx * 0.05 }}
                  className="group"
                >
                  <div className="aspect-square rounded-xl overflow-hidden mb-4 bg-soft-bg">
                    {member.image ? (
                      <img
                        src={member.image}
                        alt={member.name}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        loading="lazy"
                      />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center">
                        <span className="text-3xl font-heading font-medium text-navy/30">{member.name[0]}</span>
                      </div>
                    )}
                  </div>
                  <h3 className="font-heading font-medium text-navy">{member.name}</h3>
                  <p className="text-slate text-xs uppercase tracking-widest mt-1">{member.role}</p>
                </motion.div>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
